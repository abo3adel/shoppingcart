<?php

namespace Abo3adel\ShoppingCart\Traits\Base;

trait GetConfigKeysTrait
{
    /**
     * get default instance
     *
     * @return string
     */
    public function defaultInstance(): string
    {
        return $this->config('defaultInstance');
    }

    /**
     * get session array name
     *
     * @return string
     */
    public function sessionName(): string
    {
        return $this->config('session_name');
    }

    /**
     * get table addon
     *
     * @return string
     */
    public function tbAddon(): string
    {
        return $this->config('addon');
    }

    /**
     * first option
     *
     * @return string|null
     */
    public function fopt(): ?string
    {
        return $this->config('opt1');
    }

    /**
     * second option
     *
     * @return string|null
     */
    public function sopt(): ?string
    {
        return $this->config('opt2');
    }

    /**
     * cast first option as
     *
     * @return string
     */
    public function opt1Casts(): string
    {
        return $this->config('casts.opt1');
    }

    /**
     * cast second option as
     *
     * @return string
     */
    public function opt2Casts(): string
    {
        return $this->config('casts.opt2');
    }

    private function config(string $key): ?string
    {
        return config('shoppingcart.'. $key, null);
    }
}