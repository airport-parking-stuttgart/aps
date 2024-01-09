<?php

class AdditionalServices
{
    static function calculateProductPriceFromIds($ids) : float
    {
        $price = 0;
        if(!$ids){
            return $price;
        }
        foreach($ids as $id){
            $service = Database::getAdditionalService($id);
            $price += $service->price;
        }

        return $price;
    }
}