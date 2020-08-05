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
     * @param string $instance
     * @return self
     */
    public function instance(string $instance = null): self
    {
        $this->instance = $instance ?? $this->config('defaultInstance');
        return $this;
    }
}