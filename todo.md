# patron methods
see [api page](http://www.oclc.org/developer/develop/web-services/wms-ncip-service/patron-profile.en.html)

-[ ] `Patron::lookupUser( $userID )`

-[ ] `Patron::renewItem( $userID, $itemID )`

-[ ] `Patron::renewAllItem( $userID )`

-[ ] `Patron::renewAllItems( $userID )`
    - alias of `Patron::renewAllItem`

-[ ] `Patron::requestItem( $userID, $itemID )`

-[ ] `Patron::requestBibItem( $userID, $bibID )`

-[ ] `Patron::updateRequestItem( ??? )`

-[ ] `Patron::cancelRequestItem( $userID, $requestID )`

# staff methods

-[ ] `Staff::checkOutItem( $userID, $itemID, $opts = array() )`

-[ ] `Staff::checkInItem( $itemID, $agencyID = null )`

-[ ] `Staff::requestItem( $userID, $itemID )`

-[ ] `Staff::requestBibItem( $userID, $bibID )`

-[ ] `Staff::cancelRequestItem( $userID, $itemID )`

-[ ] `Staff::cancelRequestBibItem( $userID, $bibID )`

```php


```