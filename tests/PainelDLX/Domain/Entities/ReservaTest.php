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

namespace Reservas\PainelDLX\Tests\Domain\Entities;

use DateInterval;
use DatePeriod;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\ORMException;
use Exception;
use PainelDLX\Testes\Helpers\UsuarioTesteHelper;
use PainelDLX\Testes\TestCase\TesteComTransaction;
use Reservas\PainelDLX\Domain\Entities\Disponibilidade;
use Reservas\PainelDLX\Domain\Entities\Quarto;
use Reservas\PainelDLX\Domain\Entities\Reserva;
use Reservas\PainelDLX\Domain\Entities\ReservaHistorico;
use Reservas\PainelDLX\Domain\Exceptions\ValorMenorQueMinimoQuartoException;
use Reservas\Tests\ReservasTestCase;

/**
 * Class ReservaTest
 * @package Reservas\PainelDLX\Tests\Domain\Entities
 * @coversDefaultClass \Reservas\PainelDLX\Domain\Entities\Reserva
 */
class ReservaTest extends ReservasTestCase
{
    use TesteComTransaction;

    /**
     * @throws Exception
     */
    public function test__construct(): Reserva
    {
        $quarto = new Quarto('Teste de Quarto', 1, 10);
        $checkin = new DateTime();
        $checkout = (new DateTime())->modify('+3 days');
        $adultos = mt_rand(1, 3);

        $reserva = new Reserva($quarto, $checkin, $checkout, $adultos);

        $this->assertInstanceOf(Reserva::class, $reserva);
        $this->assertEquals($quarto, $reserva->getQuarto());
        $this->assertEquals($checkin, $reserva->getCheckin());
        $this->assertEquals($checkout, $reserva->getCheckout());
        $this->assertEquals($adultos, $reserva->getAdultos());
        $this->assertInstanceOf(Collection::class, $reserva->getHistorico());

        return $reserva;
    }

    /**
     * @param Reserva $reserva
     * @throws DBALException
     * @throws ORMException
     * @covers ::addHistorico
     * @depends test__construct
     */
    public function test_AddHistorico_deve_adicionar_um_historico_a_reserva(Reserva $reserva)
    {
        $usuario = UsuarioTesteHelper::criarDB('Funcionario', 'funcionario@aparthotel.com', '');
        $status = 'Cancelada';
        $motivo = 'Teste de motivo para cancelamento.';

        $reserva->addHistorico($status, $motivo, $usuario);

        $hasHistoricoAdicionado = $reserva->getHistorico()->exists(function ($key, ReservaHistorico $reserva_historico) use ($reserva, $status, $motivo) {
            return $reserva_historico->getStatus() === $status
                && $reserva_historico->getMotivo() === $motivo
                && $reserva_historico->getReserva() === $reserva;
        });

        $this->assertTrue($hasHistoricoAdicionado);
    }

    /**
     * @param Reserva $reserva
     * @throws DBALException
     * @throws ORMException
     * @throws ValorMenorQueMinimoQuartoException
     * @covers ::confirmada
     * @depends test__construct
     */
    public function test_Confirmada_seta_Reserva_como_confirmada_e_retirar_Disponibilidade(Reserva $reserva)
    {
        $usuario = UsuarioTesteHelper::criarDB('Funcionario', 'funcionario@aparthotel.com', '');
        // $quarto = $reserva->getQuarto();
        $quarto = new Quarto('Outro Teste', 10, 10);
        $reserva->setQuarto($quarto);

        $dt_interval = new DateInterval('P1D');
        $dt_periodo = new DatePeriod($reserva->getCheckin(), $dt_interval, $reserva->getCheckout());

        $qtde_quartos_dispon = 1;
        $qtde_quartos_esperada = $qtde_quartos_dispon - 1;

        /** @var DateTime $data */
        foreach ($dt_periodo as $data) {
            $quarto->addDispon($data, $qtde_quartos_dispon, [1 => 10]);
        }

        $reserva->confirmada('Reserva foi paga via digitação de cartão.', $usuario);

        $has_historico_confirmada = $reserva->getHistorico()->exists(function ($key, ReservaHistorico $reserva_historico) {
            return $reserva_historico->getStatus() === Reserva::STATUS_CONFIRMADA;
        });

        $has_dispon_invalida = $quarto->getDispon($reserva->getCheckin(), $reserva->getCheckout())->exists(function ($key, Disponibilidade $dispon) use ($qtde_quartos_esperada) {
            return $dispon->getQtde() !== $qtde_quartos_esperada;
        });

        $this->assertTrue($reserva->isConfirmada());
        $this->assertEquals(Reserva::STATUS_CONFIRMADA, $reserva->getStatus());
        $this->assertTrue($has_historico_confirmada);
        $this->assertFalse($has_dispon_invalida);
    }

    /**
     * @param Reserva $reserva
     * @throws DBALException
     * @throws ORMException
     * @covers ::cancelada
     * @depends test__construct
     */
    public function test_Cancelada_seta_Reserva_como_cancelada(Reserva $reserva)
    {
        $usuario = UsuarioTesteHelper::criarDB('Funcionario', 'funcionario@aparthotel.com', '');
        $reserva->cancelada('Reserva foi paga via digitação de cartão.', $usuario);

        $has_historico_cancelada = $reserva->getHistorico()->exists(function ($key, ReservaHistorico $reserva_historico) {
            return $reserva_historico->getStatus() === Reserva::STATUS_CANCELADA;
        });

        $this->assertTrue($reserva->isCancelada());
        $this->assertEquals(Reserva::STATUS_CANCELADA, $reserva->getStatus());
        $this->assertTrue($has_historico_cancelada);
    }

    /**
     * @param Reserva $reserva
     * @covers ::getTotalHospedes
     * @depends test__construct
     */
    public function test_GetTotalHospedes_deve_retornar_a_soma_dos_hospedes_adultos_e_criancas(Reserva $reserva)
    {
        $total_hospedes = $reserva->getTotalHospedes();
        $soma_hospedes = $reserva->getAdultos() + $reserva->getCriancas();

        $this->assertIsInt($total_hospedes);
        $this->assertEquals($soma_hospedes, $reserva->getTotalHospedes());
    }

    /**
     * @param Reserva $reserva
     * @covers ::calcularValor
     * @depends test__construct
     * @throws ValorMenorQueMinimoQuartoException
     */
    public function test_CalcularValor_deve_retornar_valor_total_reserva(Reserva $reserva)
    {
        $quarto = $reserva->getQuarto();

        $dt_interval = new DateInterval('P1D');
        $dt_periodo = new DatePeriod($reserva->getCheckin(), $dt_interval, $reserva->getCheckout());

        $qtde_quartos_dispon = 1;
        $valor_diaria = 10;
        $valor_total = $valor_diaria * iterator_count($dt_periodo);

        $valores_diarias = [];
        for ($i = 1; $i <= $quarto->getMaxHospedes(); $i++) {
            $valores_diarias[$i] = $valor_diaria;
        }

        /** @var DateTime $data */
        foreach ($dt_periodo as $data) {
            $quarto->addDispon($data, $qtde_quartos_dispon, $valores_diarias);
        }

        $reserva->calcularValor();
        $this->assertEquals($valor_total, $reserva->getValor());
    }
}
