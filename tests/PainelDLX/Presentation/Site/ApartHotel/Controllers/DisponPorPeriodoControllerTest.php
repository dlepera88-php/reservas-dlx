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

namespace Reservas\PainelDLX\Tests\Presentation\Site\ApartHotel\Controllers;

use DLX\Core\Configure;
use DLX\Infra\EntityManagerX;
use DLX\Infra\ORM\Doctrine\Services\DoctrineTransaction;
use PainelDLX\Application\Factories\CommandBusFactory;
use Psr\Http\Message\ServerRequestInterface;
use Reservas\PainelDLX\Presentation\Site\ApartHotel\Controllers\DisponPorPeriodoController;
use Reservas\Tests\ReservasTestCase;
use SechianeX\Factories\SessionFactory;
use Vilex\VileX;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class DisponPorPeriodoControllerTest
 * @package Reservas\PainelDLX\Tests\Presentation\Site\ApartHotel\Controllers
 * @coversDefaultClass \Reservas\PainelDLX\Presentation\Site\ApartHotel\Controllers\DisponPorPeriodoController
 */
class DisponPorPeriodoControllerTest extends ReservasTestCase
{

    /**
     * @return DisponPorPeriodoController
     * @throws \SechianeX\Exceptions\SessionAdapterInterfaceInvalidaException
     * @throws \SechianeX\Exceptions\SessionAdapterNaoEncontradoException
     * @throws \Doctrine\ORM\ORMException
     */
    public function test__construct(): DisponPorPeriodoController
    {
        $session = SessionFactory::createPHPSession();
        $session->set('vilex:pagina-mestra', 'painel-dlx-master');

        $command_bus = CommandBusFactory::create(self::$container, Configure::get('app', 'mapping'));

        $controller = new DisponPorPeriodoController(
            new VileX(),
            $command_bus(),
            $session,
            new DoctrineTransaction(EntityManagerX::getInstance())
        );

        $this->assertInstanceOf(DisponPorPeriodoController::class, $controller);

        return $controller;
    }

    /**
     * @param DisponPorPeriodoController $controller
     * @throws \Vilex\Exceptions\ContextoInvalidoException
     * @throws \Vilex\Exceptions\PaginaMestraNaoEncontradaException
     * @throws \Vilex\Exceptions\ViewNaoEncontradaException
     * @covers ::formDisponPorPeriodo
     * @depends test__construct
     */
    public function test_FormDisponPorPeriodo_deve_retornar_um_HtmlResponse(DisponPorPeriodoController $controller)
    {
        $request = $this->createMock(ServerRequestInterface::class);

        /** @var ServerRequestInterface $request */

        $response = $controller->formDisponPorPeriodo($request);
        $this->assertInstanceOf(HtmlResponse::class, $response);
    }

    /**
     * @param DisponPorPeriodoController $controller
     * @throws \Vilex\Exceptions\ContextoInvalidoException
     * @throws \Vilex\Exceptions\PaginaMestraNaoEncontradaException
     * @throws \Vilex\Exceptions\ViewNaoEncontradaException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\DBAL\DBALException
     * @covers ::disponConfigQuarto
     * @depends test__construct
     */
    public function test_DisponConfigQuarto_deve_retornar_um_HtmlResponse(DisponPorPeriodoController $controller)
    {
        $query = '
            select
                quarto_id
            from
                dlx_reservas_quartos
            where
                quarto_publicar = 1
            order by 
                rand()
        ';

        $sql = EntityManagerX::getInstance()->getConnection()->executeQuery($query);
        $id = $sql->fetchColumn();

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getQueryParams')->willReturn(['id' => $id]);

        /** @var ServerRequestInterface $request */

        $response = $controller->disponConfigQuarto($request);
        $this->assertInstanceOf(HtmlResponse::class, $response);
    }

    /**
     * @param DisponPorPeriodoController $controller
     * @throws \Vilex\Exceptions\ContextoInvalidoException
     * @throws \Vilex\Exceptions\ViewNaoEncontradaException
     * @covers ::salvarDisponPorPeriodo
     * @depends test__construct
     */
    public function test_SalvarDisponPorPeriodo_deve_retornar_um_JsonResponse(DisponPorPeriodoController $controller)
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn([
            'quarto_id' => 1,
            'data_inicial' => date('Y-m-d'),
            'data_final' => date('Y-m-d'),
            'qtde' => 1,
            'valores' => [1 => 99.]
        ]);

        /** @var ServerRequestInterface $request */

        $response = $controller->salvarDisponPorPeriodo($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
    }
}
