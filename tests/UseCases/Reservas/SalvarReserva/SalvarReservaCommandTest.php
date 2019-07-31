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

namespace Reservas\Tests\UseCases\Reservas\SalvarReserva;

use DateTime;
use Exception;
use Reservas\Domain\Quartos\Entities\Quarto;
use Reservas\Domain\Reservas\Entities\Reserva;
use Reservas\UseCases\Reservas\SalvarReserva\SalvarReservaCommand;
use Reservas\Tests\ReservasTestCase;

/**
 * Class SalvarReservaCommandTest
 * @package Reservas\Tests\UseCases\Reservas\ReservarQuarto
 * @coversDefaultClass \Reservas\UseCases\Reservas\SalvarReserva\SalvarReservaCommand
 */
class SalvarReservaCommandTest extends ReservasTestCase
{
    /**
     * @return SalvarReservaCommand
     * @throws Exception
     */
    public function test__construct(): SalvarReservaCommand
    {
        $quarto = new Quarto('Teste', 1, 10);
        $data_inicial = new DateTime();
        $data_final = (clone $data_inicial)->modify('+3 days');
        $reserva = new Reserva($quarto, $data_inicial, $data_final, 1);

        $command = new SalvarReservaCommand($reserva);

        $this->assertInstanceOf(SalvarReservaCommand::class, $command);

        return $command;
    }

    /**
     * @param SalvarReservaCommand $command
     * @covers ::getReserva
     * @depends test__construct
     */
    public function test_GetReserva(SalvarReservaCommand $command)
    {
        $this->assertInstanceOf(Reserva::class, $command->getReserva());
    }
}
