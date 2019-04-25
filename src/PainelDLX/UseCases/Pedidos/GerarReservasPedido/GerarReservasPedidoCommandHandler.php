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

namespace Reservas\PainelDLX\UseCases\Pedidos\GerarReservasPedido;


use DateTime;
use Exception;
use Reservas\PainelDLX\Domain\Entities\Quarto;
use Reservas\PainelDLX\Domain\Entities\Reserva;
use Reservas\PainelDLX\Domain\Repositories\QuartoRepositoryInterface;

class GerarReservasPedidoCommandHandler
{
    /**
     * @var QuartoRepositoryInterface
     */
    private $quarto_repository;

    /**
     * GerarReservasPedidoCommandHandler constructor.
     * @param QuartoRepositoryInterface $quarto_repository
     */
    public function __construct(QuartoRepositoryInterface $quarto_repository) {
        $this->quarto_repository = $quarto_repository;
    }

    /**
     * @param GerarReservasPedidoCommand $command
     * @throws Exception
     */
    public function handle(GerarReservasPedidoCommand $command)
    {
        $pedido = $command->getPedido();
        $itens = $pedido->getItens();

        foreach ($itens as $item) {
            /** @var Quarto $quarto */
            $quarto = $this->quarto_repository->find($item->quartoID);
            $checkin = new DateTime($item->checkin);
            $checkout = new DateTime($item->checkout);

            $reserva = new Reserva($quarto, $checkin, $checkout, $item->adultos);
            $reserva->setCriancas($item->criancas);
            $reserva->setValor($item->valor);

            // Dados do hóspede
            $reserva->setHospede($pedido->getNome());
            $reserva->setCpf($pedido->getCpf());
            $reserva->setTelefone($pedido->getTelefone());
            $reserva->setEmail($pedido->getEmail());

            $pedido->addReserva($reserva);
        }
    }
}