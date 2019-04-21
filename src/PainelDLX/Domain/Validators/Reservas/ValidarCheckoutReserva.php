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

namespace Reservas\PainelDLX\Domain\Validators\Reservas;


use Reservas\PainelDLX\Domain\Contracts\ReservaValidatorInterface;
use Reservas\PainelDLX\Domain\Entities\Reserva;
use Reservas\PainelDLX\Domain\Exceptions\DataCheckoutMenorCheckinException;

/**
 * Class ValidarCheckoutReserva
 * @package Reservas\PainelDLX\Domain\Validators\Reservas
 * @covers ValidarCheckoutReservaTest
 */
class ValidarCheckoutReserva implements ReservaValidatorInterface
{

    /**
     * Valida uma determinada regra sobre reserva
     * @param Reserva $reserva
     * @return bool
     * @throws DataCheckoutMenorCheckinException
     */
    public function validar(Reserva $reserva): bool
    {
        $dt_diff = $reserva->getCheckin()->diff($reserva->getCheckout());
        $dt_diff = (int)$dt_diff->format('%R%a');

        if ($dt_diff < 1) {
            throw new DataCheckoutMenorCheckinException($reserva->getCheckin(), $reserva->getCheckout());
        }

        return true;
    }
}