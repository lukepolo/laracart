# Upgrade Guide from 1.0 -> 2.0

## Breaking Changes

### Cart Item

* Removed `price` , I suggest using `subTotal` instead.
* Removed `netTotal` -- probably will put back in

* `addSubItem(array $subItem, $autoUpdate = true)` -> `addSubItem(array $subItem)`
* `subTotal($format = true, $withDiscount = true, $taxedItemsOnly = false, $withTax = false)` -> `subTotal($format = true)`
* `subItemsTotal($format = true, $taxedItemsOnly = false, $withTax = false)` -> `subItemsTotal($format = true)`
* `tax($amountNotTaxable = 0, $grossTax = true, $rounded = false, $withDiscount = true)` -> `tax($format = true)`

### Cart Sub Item

* Removed `price` , I suggest using `subTotal` instead.

### Coupons

* `code` removed
* `forItem` removed
* `discount($throwErrors = false)` -> `discount()`
* `getFailedMessage` removed
  
### LaraCart

* `total($format = true, $withDiscount = true, $withTax = true, $withFees = true)` -> `total($format = true);`
* `subTotal($format = true, $withDiscount = true)` -> `subTotal($format = true);`
* `feeTotals($format = true);` -> `feeSubTotal($format = true);`
* `taxTotal($format = true, $withFees = true, $grossTaxes = true, $withDiscounts = true)` -> `taxTotal($format = true)`
* `totalDiscount($format = true, $withItemDiscounts = true)` -> `discountTotal($format = true)`

### Config

* `tax_by_item` removed (now the default)
* `tax_item_before_discount` removed
* `round_every_item_price` removed (now the default)
* `discountTaxable` removed
* `discountsAlreadyTaxed` removed
* `discountOnFees` -> `discount_fees`
* `fees_taxable` added