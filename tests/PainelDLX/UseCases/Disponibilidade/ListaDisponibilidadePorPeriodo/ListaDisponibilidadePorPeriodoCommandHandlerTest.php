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

namespace Reservas\PainelDLX\Tests\PainelDLX\UseCases\Disponibilidade\ListaDisponibilidadePorPeriodo;

use DateTime;
use DLX\Infra\EntityManagerX;
use Reservas\PainelDLX\Domain\Entities\Disponibilidade;
use Reservas\PainelDLX\Domain\Entities\Quarto;
use Reservas\PainelDLX\Domain\Repositories\DisponibilidadeRepositoryInterface;
use Reservas\Tests\ReservasTestCase;
use Reservas\PainelDLX\UseCases\Disponibilidade\ListaDisponibilidadePorPeriodo\ListaDisponibilidadePorPeriodoCommand;
use Reservas\PainelDLX\UseCases\Disponibilidade\ListaDisponibilidadePorPeriodo\ListaDisponibilidadePorPeriodoCommandHandler;

/**
 * Class ListaDisponibilidadePorPeriodoCommandHandlerTest
 * @package Reservas\PainelDLX\Tests\PainelDLX\UseCases\Disponibilidade\ListaDisponibilidadePorPeriodo
 * @coversDefaultClass \Reservas\PainelDLX\UseCases\Disponibilidade\ListaDisponibilidadePorPeriodo\ListaDisponibilidadePorPeriodoCommandHandler
 */
class ListaDisponibilidadePorPeriodoCommandHandlerTest extends ReservasTestCase
{
    /**
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    public function gerarHandler(): array
    {
        self::setUp();

        /** @var DisponibilidadeRepositoryInterface $disponibilidade_repository */
        $disponibilidade_repository = EntityManagerX::getRepository(Disponibilidade::class);
        $handler = new ListaDisponibilidadePorPeriodoCommandHandler($disponibilidade_repository);

        return [[$handler]];
    }

    /**
     * @param ListaDisponibilidadePorPeriodoCommandHandler $handler
     * @dataProvider gerarHandler
     * @covers ::handle
     * @throws \Exception
     */
    public function test_Handle(ListaDisponibilidadePorPeriodoCommandHandler $handler)
    {
        $data_inicial = new DateTime();
        $data_final = (clone $data_inicial)->modify('+1 week');

        $command = new ListaDisponibilidadePorPeriodoCommand($data_inicial, $data_final);
        $lista = $handler->handle($command);

        $this->assertIsArray($lista);

        if (count($lista) > 0) {
            /** @var Disponibilidade $dispon */
            foreach ($lista as $dispon) {
                $this->assertInstanceOf(Disponibilidade::class, $dispon);
            }
        } else {
            $this->markTestIncomplete('Nenhuma disponibilidade encontrada para completar o teste.');
        }
    }
}
