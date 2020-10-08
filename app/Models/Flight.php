<?php
namespace App\Models;

class Flight {

   private $id;
   private $cia;
   private $fare;
   private $flightNumber;
   private $origin;
   private $destination;
   private $departureDate;
   private $arrivalDate;
   private $departureTime;
   private $arrivalTime;
   private $classService;
   private $price;
   private $tax;
   private $outbound;
   private $inbound;
   private $duration;

    public function __construct($array)
    {       
        foreach ($array as $key => $value)
        {
            $this->$key = $value;
        }
    }
 
}