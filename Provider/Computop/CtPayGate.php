<?php

namespace Astina\Bundle\PaymentBundle\Provider\Computop;

class CtPayGate extends CtBlowfish
{

    /* class initialize
    parent ctBlowfish
    @access private */
    function __construct() {
        parent::__construct();
        $this->text = array(
            'nodata' => "No data found!",
            'paymentfailed' => "Payment failed!",
            'paymentsuccessful' => "Payment successful!",
            'unknownstatus' => "Unknown status!",
        );
    }

    /* search
    @param string searchstring
    @return string realstatus
    @access public */
    function ctRealstatus($sStatus){
        // string ctRealstatus(string $sStatus(searchstring))

        switch($sStatus){

            case "OK":
                $rs = $this->text['paymentsuccessful'];   // correct response

            case "AUTHORIZED":
                $rs = $this->text['paymentsuccessful'];   // correct response
                break;

            case "FAILED":
                $rs = $this->text['paymentfailed'];       // correct response
                break;

            case "":
                $rs = $this->text['nodata'];              // no data available
                break;

            default:
                $rs = $this->text['unknownstatus'];       // different status
        }

        return $rs;
    }

    /* split and maybe search
    @param array haystack
    @param string needle
    @param string search
    @return string realstatus
    @access public */
    function ctSplit($arText, $sSplit, $sArg = ""){
        // string ctSplit(array $arText(haystack), string $sSplit(needle), [string sArg(search for)])

        $b = "" ; $i = 0;

        $info = '';

        while($i < count ($arText)){
            $b = explode($sSplit, $arText [$i++]);

            if($b[0] == $sArg){                // check for $sArg
                $info = $b[1];
                $b = 0;
                break;

            } else {
                $info .= '<tr><td align=right>'.$b[0].'</td><td>"'.$b[1].'"</td></tr>';
            }
        }

        if((strlen($sArg) > 0) & ($b != 0)){   // $sArg not found
            $info = "";
        }

        return $info;
    }
}
