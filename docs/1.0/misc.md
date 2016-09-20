# Miscellaneous 

<a name="cross-browser-support"></a>
## Cross Browser Support
You must have the LaraCart database migration and migrate. To enable just change it in your config!

    'cross_devices' => true

> {danger} You must be using the Auth Manager, and may have to update the migration to fit your needs. 

<a name="cart-events"></a>
## Events 
    <table class="table table-bordered">
        <thead>
            <th>Event</th>
            <th>Functions That fire event</th>
        </thead>
        <tbody>
            <tr>
                <td>laracart.new</td>
                <td>LaraCart::setInstance()</td>
            </tr>
            <tr>
                <td>laracart.update</td>
                <td>LaraCart::update()</td>
            </tr>
            <tr>
                <td>laracart.addItem($item)</td>
                <td>LaraCart::add() , LaraCart::addLine()</td>
            </tr>
            <tr>
                <td>laracart.updateItem($item)</td>
                <td>LaraCart::generateHash()</td>
            </tr>
            <tr>
                <td>laracart.removeItem($cartInstance)</td>
                <td>LaraCart::removeItem()</td>
            </tr>
            <tr>
                <td>laracart.empty($cartInstance)</td>
                <td>LaraCart::emptyCart()</td>
            </tr>
            <tr>
                <td>laracart.destroy($cartInstance)</td>
                <td>LaraCart::destroyCart()</td>
            </tr>
        </tbody>
    </table>
<a name="cart-exceptions"></a>
## Exceptions
    <table class="table table-bordered">
        <thead>
            <th>Exception</th>
            <th>Reason</th>
        </thead>
        <tbody>
            <tr>
                <td>InvalidPrice</td>
                <td>When trying to give an item a non currency format</td>
            </tr>
            <tr>
                <td>InvalidQuantity</td>
                <td>When trying to give an item a non-integer for a quantity</td>
            </tr>
            <tr>
                <td>CouponException</td>
                <td>When a coupon either is expired or an invalid amount</td>
            </tr>
            <tr>
                <td>ModelNotFound</td>
                <td>When you try to relate a model that does not exist</td>
            </tr>
            <tr>
                <td>InvalidTaxableValue</td>
                <td>Either a tax value is invalid or taxable is not a boolean</td>
            </tr>
        </tbody>
    </table>
</section>