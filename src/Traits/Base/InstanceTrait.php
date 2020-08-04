<?php

namespace Abo3adel\ShoppingCart\Traits\Base;

trait InstanceTrait {
    private $instance;

    /**
     * get cart current instance
     *
     * @return string|null
     */
    public function getInstance(): ?string
    {
        return $this->instance ?? null;
    }

    /**
     * set cart instance
     *
     * @param string|null $instance
     * @return self
     */
    public function instance(?string $instance): self
    {
        $this->instance = $instance;
        return $this;
    }
}