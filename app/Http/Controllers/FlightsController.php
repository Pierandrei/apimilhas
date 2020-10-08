<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
//use App\Models\Flight;

class FlightsController extends Controller {
    
    /**
     * Método request da API e return response of rules
     * 
     * @return Json
     */
    public function getflight()
    {
        $client = new Client();

        $request = $client->get('http://prova.123milhas.net/api/flights');
        $response = $request->getBody()->getContents();
        $data = json_decode( $response , true);

        return json_encode($this->rules( $data));
    }


    /**
     * Método regra de negócio
     * -Deve-se gerar grupos com uma ou mais opções de ida e volta;
     * -Dentro de um mesmo grupo não podem ter voos de tarifas diferentes;
     * -Ao formar um grupo é necessário criar um identificador único e ter um preço total;
     * 
     * @param Array $data
     * @return Array
     */
    private function rules($data){
       $groups = array('flights'=>array(), 
                        'groups'=>array(),
                        'totalGroups'=>0,
                        'totalFlights'=>0,
                        'cheapestPrice'=>0,  
                        'cheapestGroup'=>0);
        $arrayoutbound  = array();
        $arrayinbound  = array();
        
        foreach($data as $obj){
            if(!array_key_exists('flightNumber', $obj))
                continue;

            if(!array_key_exists($obj['flightNumber'], $groups['flights']))
                array_push($groups['flights'], $obj['flightNumber']);
                        
            $key = $obj['fare']."_".$obj['price'];

            if($obj['outbound']){ // voo de ida
                if(!array_key_exists($key, $arrayoutbound ))
                    $arrayoutbound[$key] = array( );
                
                array_push($arrayoutbound[$key], $obj['id']);
            }
            else{ // voo de volta
                if(!array_key_exists($key, $arrayinbound ))
                $arrayinbound[$key] = array( );
            
                array_push($arrayinbound[$key], $obj['id']);
            }
        }


        $groups['totalFlights'] =  count($groups['flights']);
        $cheapestPrice = PHP_INT_MAX;
        $cheapestGroup = $contuniquegroup = 0;

        $groupx = array('uniqueId' => null, 
                        'totalPrice' => null, 
                        'outbound'=>array(), 
                        'inbound'=>array() );
        foreach($arrayoutbound as $keyout=>$outbound){

            $indexout = explode("_", $keyout);
            $fareout = $indexout[0];
            $priceout = $indexout[1];

            foreach($arrayinbound as $keyin=>$inbound){
                $indexin = explode("_", $keyin);
                $farein = $indexin[0];
                $pricein = $indexin[1];

                if($fareout == $farein){
                    $group = $groupx;
                    //$group['uniqueId'] = ++$contuniquegroup;
                    $group['totalPrice'] = $priceout + $pricein;
                    $group['outbound'] = $outbound;
                    $group['inbound'] = $inbound;

                    if($group['totalPrice'] < $cheapestPrice)
                    {
                        $cheapestPrice = $group['totalPrice'];
                        //$cheapestGroup = $contuniquegroup;
                    }
                    array_push($groups['groups'],  $group);
                }
            }
        }
        
        $treste = usort($groups['groups'], function ($a, $b){
            return $a['totalPrice'] - $b['totalPrice'];
        });

        for($i=0;$i<count($groups['groups']); $i++)        
            $groups['groups'][$i]['uniqueId'] = $i+1;
        $cheapestGroup = 1;

        $groups['totalGroups'] =  count($groups['groups']);
        $groups['cheapestPrice'] =  $cheapestPrice;
        $groups['cheapestGroup'] =  $cheapestGroup;
        //$groups['flights'] = implode( ",", $groups['flights'] );

        return $groups;
    }
}

