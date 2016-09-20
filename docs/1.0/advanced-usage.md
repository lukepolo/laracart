# Advanced Usage

<a name="sub-items"></a>
## Sub Items
The reasoning behind sub items is to allow you add additional items without the all the necessary things that a regular item needs. For instance if you really wanted the same item but in a different size and that size costs more, you can add it as a sub item so it calculates in the price.

    $item = LaraCart::add(2, 'Shirt', 1, 15.99, [
        'size' => 'XXL'
    ]);

    $item->addSubItem([
        'description' => 'Extra Cloth Cost', // this line is not required!
        'price' => 3.00
    ]);

    $item->subTotal(); // $18.99
    $item->subItemsTotal($formatMoney = true); // $3.00

<a name="item-model-bindings"></a>
## Item Model Binding
You can set a default model relation along with its sub-relations to an item by setting it in your config item_model.

> {warning} This will fetch your model based on the items id stored in the cart 

    \LukePOLO\LaraCart\CartItem::ITEM_OPTIONS => [
        'your_key' => 'price_relation.value' // this will go to the price relation then get the value!
        'your_other_key' => 'price_relation.sub_relation.value' // This also works
    ]

    $item = LaraCart::add($itemID = 123123123);
    $item = LaraCart::add(Illuminate\Database\Eloquent\Model $itemModel);