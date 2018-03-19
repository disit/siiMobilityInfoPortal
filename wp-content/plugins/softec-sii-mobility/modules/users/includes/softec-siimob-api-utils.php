<?php

/**
 *    Classe di response per le chiamate ajax
 */

if ( ! function_exists( 'add_action' ) ) {
    exit;
}

class ApiResponse
{
    private $aData;
    private $aError;

    function __construct()
    {
        $this->aData = [];
        $this->aError = [];
    }

    public function setError($sErrorMessage)
    {
        $this->aError[] = $sErrorMessage;
    }

    public function setErrors($aErrorsMessage)
    {
        foreach ($aErrorsMessage as $sErrorMessage) {
            $this->setError($sErrorMessage);        
        }
    }

    public function setData($sKey, $sValue)
    {
        $this->aData[$sKey] = $sValue;
    }

    public function getReturn()
    {
        return array(
            'data' => $this->aData,
            'error' => $this->aError
        );
    }

    public function hasErrors()
    {
        return ! empty($this->aError);
    }
}