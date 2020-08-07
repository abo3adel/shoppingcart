<?php

namespace Abo3adel\ShoppingCart\Contracts;

interface CanBeBought
{
    /**
     * get model price
     *
     * @return float
     */
    public function getPrice(): float;

    /**
     * get discount percentage
     *
     * @return float
     */
    public function getDiscount(): float;
}