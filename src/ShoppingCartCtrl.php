<?php

namespace Abo3adel\ShoppingCart;

use Abo3adel\ShoppingCart\Events\CartInstanceDestroyed;
use Abo3adel\ShoppingCart\Events\CartItemRemoved;
use Abo3adel\ShoppingCart\Traits\Base\AddingMethod;
use Abo3adel\ShoppingCart\Traits\Base\GetConfigKeysTrait;
use Abo3adel\ShoppingCart\Traits\Base\Helpers;
use Abo3adel\ShoppingCart\Traits\Base\InstanceTrait;
use Abo3adel\ShoppingCart\Traits\Base\UpdatingItemsMethod;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ShoppingCartCtrl
{
    use InstanceTrait,
        GetConfigKeysTrait,
        AddingMethod,
        UpdatingItemsMethod,
        Helpers;

    /**
     * tax percentage
     *
     * @var int
     */
    private $tax;

    /**
     * manage this user items
     *
     * @var User
     */
    private $user = null;

    public function __construct()
    {
        $this->instance = $this->instance ?? $this->config('defaultInstance');

        if (!session()->has($this->sessionName())) {
            session()->put($this->sessionName(), []);
        }

        $this->tax = $this->getDefaultTax();
    }

    /**
     * retrive item from cart
     * 
     * @example find(5)
     * @example find(2, 'App\Product')
     *
     * @param integer $itemId
     * @param string|null $buyableType
     * @return CartItem|null
     */
    public function find(
        int $itemId,
        ?string $buyableType = null
    ): ?CartItem {
        if ($this->checkAuth()) {
            if (null !== $buyableType) {
                return CartItem::whereInstance($this->instance)
                    ->whereUserId($this->user->id)
                    ->where('buyable_id', $itemId)
                    ->where('buyable_type', $buyableType)
                    ->first();
            }

            return CartItem::find($itemId);
        }

        $item = collect(session($this->sessionName()))
            ->whereStrict('instance', $this->instance);

        if (null !== $buyableType) {
            $item = $item->whereStrict('buyable_id', $itemId)
                ->whereStrict('buyable_type', $buyableType)
                ->first();
        } else {
            $item = $item->whereStrict('id', $itemId)
                ->first();
        }

        $cartItem = is_array($item) ? new CartItem($item) : $item;

        if ($cartItem) {
            $cartItem->loadMissing('buyable');
        }

        return  $item['buyable_type'] ? $cartItem : null;
    }

    /**
     * check if item exists in cart
     * 
     * @param integer $itemId
     * @param string|null $buyableType
     * @return boolean
     */
    public function has(
        int $itemId,
        ?string $buyableType = null
    ): bool {
        if ($this->checkAuth()) {
            return !!($this->find($itemId, $buyableType));
        }

        return collect(session($this->sessionName()))
            ->whereStrict('instance', $this->instance)
            ->contains(function ($val) use (
                $itemId,
                $buyableType
            ) {
                if (null !== $buyableType) {
                    return $val['buyable_id'] === $itemId &&
                        $val['buyable_type'] === $buyableType;
                }

                return $val['id'] === $itemId;
            });
    }

    /**
     * retrive cart content
     *
     * @return Collection
     */
    public function content(): Collection
    {
        if ($this->checkAuth()) {
            return CartItem::whereInstance($this->instance)
                ->whereUserId($this->user->id)
                ->get();
        }

        return collect(session($this->sessionName()))
            ->whereStrict('instance', $this->instance)
            ->map(function ($item) {
                $item = is_array($item) ? new CartItem($item) : $item;
                $item->loadMissing('buyable');
                return $item;
            });
    }

    /**
     * remove item from cart
     *
     * @param integer $itemId
     * @return boolean
     */
    public function delete(int $itemId): bool
    {
        if ($this->checkAuth()) {
            $item = $this->find($itemId);

            if ($item->delete()) {
                event(new CartItemRemoved($item));
                return true;
            }

            return false;
        }

        $items = collect(session($this->sessionName()))
            ->filter(function ($item) use ($itemId) {
                $item = (object) $item;

                if ($item->instance === $this->instance) {
                    if ($item->id === $itemId) {
                        event(new CartItemRemoved(
                            new CartItem((array) $item)
                        ));
                    }
                    return $item->id !== $itemId;
                }
                return $item;
            });

        session([$this->sessionName() => $items]);
        return true;
    }

    /**
     * delete all items with current instance
     *
     * @return boolean
     */
    public function destroy(): bool
    {
        if ($this->checkAuth()) {
            $deleted = CartItem::whereInstance($this->instance)
                ->whereUserId($this->user->id)
                ->delete();

            if ($deleted) {
                event(new CartInstanceDestroyed($this->instance));
                return true;
            }

            return false;
        }

        $items = collect(session($this->sessionName()))
            ->filter(function ($item) {
                $item = (object) $item;
                return $item->instance !== $this->instance;
            });

        event(new CartInstanceDestroyed($this->instance));
        session([$this->sessionName() => $items]);
        return true;
    }

    /**
     * merge session cart items and save into database
     *
     * @param User $user
     * @return void
     */
    public function afterLogin(User $user): void
    {
        $items = collect(session($this->sessionName()));

        $items->each(function ($item) use ($user) {
            $item = (object) $item;
            $item = (object) $item;

            $toBeUpdated = [
                'qty' => $item->qty,
                'price' => $item->price,
                'options' => json_encode($item->options),
                // 'instance' => $this->instance
            ];

            $opt1 = Cart::fopt();
            $opt2 = Cart::sopt();

            if ($opt1) {
                $toBeUpdated += [$opt1 => $item->{$opt1}];
            }

            if ($opt2) {
                $toBeUpdated += [$opt2 => $item->{$opt2}];
            }

            DB::disableQueryLog();
            DB::beginTransaction();
            $item = DB::table(Cart::tbName())->updateOrInsert([
                'instance' => $item->instance,
                'user_id' => $user->id,
                'buyable_id' => $item->buyable_id,
                'buyable_type' => $item->buyable_type,
            ], $toBeUpdated);
            DB::commit();
        });

        session([$this->sessionName() => []]);
    }

    /**
     * remove items with outofstock buyable objets
     * and lower items qty if exceeded buyable qty
     *
     * @return array
     */
    public function checkBuyableStockAmount(): array
    {
        $items = $this->content();

        // will hold deleted items after 
        // thier buyable object is out of stock
        $outOfStockErrors = [];

        // will hold items that it`s qty exceeded their
        // buyable qty and updated values from & to
        $buyableAmountErrors = [];

        // the updated cart items after deleteing or lowering qty
        $updatedItems = [];

        // check if user is loggedIn
        $loggedIn = $this->checkAuth();

        foreach ($items as $item) {
            if (!$loggedIn) {
                $item->load('buyable');
            }
            $buyableQty = $item->buyable->qty;

            // check if item qty exceeded buyable stock
            if ($item->qty > $buyableQty) {
                // if buyable is out of stock
                if ($buyableQty < 1) {
                    if ($loggedIn) {
                        // delete item from db
                        CartItem::destroy($item->id);
                    }

                    $outOfStockErrors[] = $item;

                    // do not add item to updated items list
                    continue;
                }

                $diffrenece = $item->qty - $buyableQty;
                $currentItemQty = $item->qty;
                // set item qty to diffrenece if it lower than
                // product qty otherwise to 1
                $item->qty = $diffrenece <= $buyableQty
                    ? $diffrenece
                    : 1;

                if ($loggedIn) {
                    // update item in database
                    // $this->update($item->id, $item->qty);
                    $item->update();
                }

                $buyableAmountErrors[] = (object) [
                    'from' => $currentItemQty,
                    'to' => $item->qty,
                    'item' => $item
                ];
            }
            $updatedItems[] = $item;
        }

        if (!$loggedIn) {
            session([$this->sessionName() => $updatedItems]);
        }

        return [$outOfStockErrors, $buyableAmountErrors];
    }

    /**
     * refresh item buyable object
     *
     * @return void
     */
    public function refreshItemsBuyableObjects(): void
    {
        if ($this->checkAuth()) {
            return;
        }

        $items = collect(session($this->sessionName()))->map(
            function ($item) {
                $item = is_array($item) ?
                    new CartItem($item) :
                    $item;

                $item->load('buyable');
                return $item;
            }
        );

        session([$this->sessionName() => $items]);
    }

    /**
     * remove older cart items than configured deleteAfter value
     *
     * @param integer $period count of deleted rows
     * @return integer
     */
    public function removeOldCartItems(int $period): int
    {
        if (!$period) return 0;

        $affected = DB::table(Cart::tbName())
            ->where(
                'updated_at',
                '<',
                Carbon::now()->subDays($period)
            )->delete();

        return $affected;
    }

    /**
     * check if current user is logedIn
     *
     * @return boolean
     */
    private function checkAuth(): bool
    {
        if ($this->user) return true;

        $guard = Cart::getDefaultGuard();

        if (auth($guard)->check()) {
            $this->forUser(auth($guard)->user());

            return true;
        }

        return false;
    }
}
