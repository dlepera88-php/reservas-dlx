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

namespace Reservas\PainelDLX\Tests\PainelDLX\UseCases\Quartos\SalvarQuarto;

use DLX\Infra\EntityManagerX;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\ORMException;
use Exception;
use PainelDLX\Testes\TestCase\TesteComTransaction;
use Reservas\PainelDLX\Domain\Quartos\Entities\Quarto;
use Reservas\PainelDLX\Domain\Quartos\Repositories\QuartoRepositoryInterface;
use Reservas\Tests\ReservasTestCase;
use Reservas\PainelDLX\UseCases\Quartos\SalvarQuarto\SalvarQuartoCommand;
use Reservas\PainelDLX\UseCases\Quartos\SalvarQuarto\SalvarQuartoCommandHandler;

/**
 * Class SalvarQuartoCommandHandlerTest
 * @package Reservas\PainelDLX\Tests\PainelDLX\UseCases\Quartos\SalvarQuarto
 * @coversDefaultClass \Reservas\PainelDLX\UseCases\Quartos\SalvarQuarto\SalvarQuartoCommandHandler
 */
class SalvarQuartoCommandHandlerTest extends ReservasTestCase
{
    use TesteComTransaction;

    /**
     * @return SalvarQuartoCommandHandler
     * @throws ORMException
     */
    public function test__construct(): SalvarQuartoCommandHandler
    {
        /** @var QuartoRepositoryInterface $quarto_repository */
        $quarto_repository = EntityManagerX::getRepository(Quarto::class);
        $handler = new SalvarQuartoCommandHandler($quarto_repository);

        $this->assertInstanceOf(SalvarQuartoCommandHandler::class, $handler);

        return $handler;
    }


    /**
     * @param SalvarQuartoCommandHandler $handler
     * @return Quarto
     * @throws DBALException
     * @throws ORMException
     * @throws Exception
     * @covers ::handle
     * @depends test__construct
     */
    public function test_Handle_deve_retornar_Quarto_com_ID_gerado(SalvarQuartoCommandHandler $handler): Quarto
    {
        $quarto = new Quarto('Teste de Quarto', 5, 100);
        $quarto
            ->setTamanhoM2(35)
            ->setLink('teste/teste-url');

        $command = new SalvarQuartoCommand($quarto);
        $handler->handle($command);

        $this->assertNotNull($quarto->getId());
        $this->assertIsInt($quarto->getId());

        // Verificar se o registro foi salvo no banco de dados
        $query = 'select quarto_id from dlx_reservas_quartos where quarto_id = :id';
        $sql = EntityManagerX::getInstance()->getConnection()->prepare($query);
        $sql->bindValue(':id', $quarto->getId());
        $sql->execute();

        $this->assertEquals(1, $sql->rowCount());

        return $quarto;
    }

    /**
     * @param SalvarQuartoCommandHandler $handler
     * @throws DBALException
     * @throws ORMException
     * @covers ::handle
     * @depends test__construct
     */
    public function test_Handle_deve_alterar_informacoes_no_BD(SalvarQuartoCommandHandler $handler)
    {
        /** @var Quarto $quarto */
        $quarto = $this->test_Handle_deve_retornar_Quarto_com_ID_gerado($handler);

        // Alterar algumas informações do quarto
        $nome = 'QUARTO TESTE UNITÁRIO';
        $quarto->setNome($nome);

        $command = new SalvarQuartoCommand($quarto);
        $handler->handle($command);

        /** @var Quarto $quarto_atualizado */
        $quarto_atualizado = EntityManagerX::getRepository(Quarto::class)->find($quarto->getId());
        $this->assertEquals($nome, $quarto_atualizado->getNome());
    }
}
