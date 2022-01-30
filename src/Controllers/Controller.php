<?php
namespace AugmentedSteam\Server\Controllers;

use IsThereAnyDeal\Database\DbDriver;
use Psr\Http\Message\ResponseFactoryInterface;

abstract class Controller
{
    protected ResponseFactoryInterface $responseFactory;
    protected DbDriver $db;

    public function __construct(ResponseFactoryInterface $responseFactory, DbDriver $db) {
        $this->responseFactory = $responseFactory;
        $this->db = $db;
    }
}
