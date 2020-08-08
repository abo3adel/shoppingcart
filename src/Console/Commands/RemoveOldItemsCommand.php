<?php

namespace Abo3adel\ShoppingCart\Console\Commands;

use Abo3adel\ShoppingCart\Cart;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RemoveOldItemsCommand extends Command
{

    /**
     * exclude from the list of artisan commands
     *
     * @var boolean
     */
    protected $hidden = true;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shoppingcart:destroy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'remove cart items that are older than configured deleteAfter value';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $deleteAfter = Cart::getDeletePeriod();

        $this->info(
            'Deleting cart items older than ' .
                Carbon::now()->subDays($deleteAfter)->format(
                    'd-M-Y H:i:s'
                )
        );

        $deleted = Cart::removeOldCartItems($deleteAfter);

        $this->info($deleted . ' Items was Deleted');
    }
}
