<?php

/**
 * Pfm exceptions
 */
class PfmException extends Exception {
    public $sendReports = true;
}

class PfmRoutingException extends PfmException {
}

class PfmDbException extends PfmException {
}

class PfmAuthException extends PfmException {
    public $sendReports = false;

    public function __construct($msg, $code=403) {
        parent::__construct($msg, $code);
    }
}

class PfmFileException extends PfmException {
}

class PfmParamException extends PfmException {

    public function __construct($msg, $code=422) {
        parent::__construct($msg, $code);
    }
}

class PfmUserException extends PfmException {
    public $sendReports = false;
}
