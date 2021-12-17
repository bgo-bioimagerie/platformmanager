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
}

class PfmFileException extends PfmException {
}

class PfmParamException extends PfmException {
}

class PfmUserException extends PfmException {
    public $sendReports = false;
}
