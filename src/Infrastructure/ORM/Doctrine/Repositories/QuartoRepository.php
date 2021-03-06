<?php
/**
 * MIT License
 *
 * Copyright (c) 2018 PHP DLX
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Reservas\Infrastructure\ORM\Doctrine\Repositories;


use DateTime;
use DLX\Infrastructure\ORM\Doctrine\Repositories\EntityRepository;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Reservas\Domain\Disponibilidade\Entities\Disponibilidade;
use Reservas\Domain\Quartos\Entities\Quarto;
use Reservas\Domain\Quartos\Exceptions\QuartoIndisponivelException;
use Reservas\Domain\Quartos\Repositories\QuartoRepositoryInterface;

/**
 * Class QuartoRepository
 * @package Reservas\Infrastructure\ORM\Doctrine\Repositories
 * @covers QuartoRepositoryTest
 */
class QuartoRepository extends EntityRepository implements QuartoRepositoryInterface
{
    /**
     * Verificar se existe outro quarto com o mesmo nome.
     * @param Quarto $quarto
     * @return bool
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function existsOutroQuartoComMesmoNome(Quarto $quarto): bool
    {
        $qb = $this->createQueryBuilder('q')
            ->select('COUNT(q.id)')
            ->where('q.deletado = 0')
            ->andWhere('q.nome = :nome')
            ->andWhere('q.id <> :quarto_id')
            ->setParameter(':nome', $quarto->getNome(), ParameterType::STRING)
            ->setParameter(':quarto_id', $quarto->getId(), ParameterType::INTEGER);

        $query = $qb->getQuery();

        return $query->getSingleScalarResult() > 0;
    }

    /**
     * Verificar se existe outro quarto com mesmo link.
     * @param Quarto $quarto
     * @return bool
     */
    public function existsOutroQuartoComMesmoLink(Quarto $quarto): bool
    {
        $lista = $this->findBy(['link' => $quarto->getLink()]);

        return !empty(array_filter($lista, function (Quarto $quarto_lista) use ($quarto) {
            return $quarto_lista->getId() !== $quarto->getId();
        }));
    }

    /**
     * Procurar quartos no bd que estejam disponíveis para um determinado período
     * @param DateTime $checkin
     * @param DateTime $checkout
     * @param int $qtde_hospedes
     * @param int $qtde_quartos
     * @return array
     */
    public function findQuartosDisponiveis(DateTime $checkin, DateTime $checkout, int $qtde_hospedes, int $qtde_quartos): array
    {
        $qtde_diarias_desejadas = $checkin->diff($checkout)->days;

        $qb = $this->createQueryBuilder('q')
            ->innerJoin(Disponibilidade::class, 'd', Join::WITH, 'q.id = d.quarto')
            ->where('d.data between :dt_checkin and :dt_checkout')
                ->andWhere('d.quantidade >= :qtde_quartos')
                ->andWhere('q.maximo_hospedes >= :qtde_hospedes')
            ->groupBy('q.id')
                ->addGroupBy('q.nome')
                ->addGroupBy('q.descricao')
                ->addGroupBy('q.maximo_hospedes')
                ->addGroupBy('q.quantidade')
                ->addGroupBy('q.valor_minimo')
                ->addGroupBy('q.tamanho_m2')
                ->addGroupBy('q.link')
            ->having('count(d.data) >= :qtde_diarias')
            ->setParameter(':dt_checkin', $checkin->format('Y-m-d'), ParameterType::STRING)
            ->setParameter(':dt_checkout', $checkout->format('Y-m-d'), ParameterType::STRING)
            ->setParameter(':qtde_quartos', $qtde_quartos, ParameterType::INTEGER)
            ->setParameter(':qtde_hospedes', $qtde_hospedes, ParameterType::INTEGER)
            ->setParameter(':qtde_diarias', $qtde_diarias_desejadas, ParameterType::INTEGER);

        $quartos = $qb->getQuery()->getResult();

        // Retirar quartos que tiverem alguma disponibilidade inválida
        $quartos = array_filter($quartos, function (Quarto $quarto) use ($checkin, $checkout) {
            try {
                $quarto->isDisponivelPeriodo($checkin, $checkout);
                return true;
            } catch (QuartoIndisponivelException $e) {
                return false;
            }
        });

        return $quartos;
    }

    /**
     * Gerar as disponibilidades de um quarto
     * @param Quarto $quarto
     * @param DateTime $dt_inicial
     * @param DateTime $dt_final
     * @throws DBALException
     * @throws Exception
     */
    public function gerarDisponibilidadesQuarto(Quarto $quarto, DateTime $dt_inicial, DateTime $dt_final): void
    {
        $query = 'call gerar_calendario (:dt_inicial, :dt_final, :quarto)';

        $sql = $this->_em->getConnection()->prepare($query);
        $sql->bindValue(':dt_inicial', $dt_inicial->format('Y-m-d'), ParameterType::STRING);
        $sql->bindValue(':dt_final', $dt_final->format('Y-m-d'), ParameterType::STRING);
        $sql->bindValue(':quarto', $quarto->getId(), ParameterType::INTEGER);

        $sql->execute();
    }

    /**
     * @inheritDoc
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function hasReservas(Quarto $quarto): bool
    {
        $query = '
            select
                *
            from
                reservas.Reserva
            where
                quarto_id = :quarto_id
        ';

        $sql = $this->_em->getConnection()->prepare($query);
        $sql->bindValue(':quarto_id', $quarto->getId(), ParameterType::INTEGER);
        $sql->execute();

        return $sql->rowCount() > 0;
    }
}