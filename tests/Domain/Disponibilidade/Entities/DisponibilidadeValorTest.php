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

namespace Reservas\Tests\Domain\Disponibilidade\Entities;

use Reservas\Domain\Disponibilidade\Entities\DisponibilidadeValor;
use Reservas\Tests\ReservasTestCase;

/**
 * Class DisponValorTest
 * @package Reservas\Tests\Domain\Entities
 * @coversDefaultClass \Reservas\Domain\Disponibilidade\Entities\DisponibilidadeValor
 */
class DisponibilidadeValorTest extends ReservasTestCase
{
    /**
     * @return DisponibilidadeValor
     */
    public function test__construct(): DisponibilidadeValor
    {
        $qtde_pessoas = mt_rand(1, 10);
        $valor = 12.34;

        $dispon_valor = new DisponibilidadeValor($qtde_pessoas, $valor);

        $this->assertInstanceOf(DisponibilidadeValor::class, $dispon_valor);
        $this->assertEquals($qtde_pessoas, $dispon_valor->getQuantidadePessoas());
        $this->assertEquals($valor, $dispon_valor->getValor());

        return $dispon_valor;
    }
}
