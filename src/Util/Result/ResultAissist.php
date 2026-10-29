<?php

namespace HBM\HelperBundle\Util\Result;

use HBM\BasicsBundle\Util\Result\Result as BaseResult;
use HBM\BasicsBundle\Util\Result\Traits\ResultArrayAccessTrait;
use HBM\BasicsBundle\Util\Result\Traits\ResultErrorTrait;
use HBM\BasicsBundle\Util\Result\Traits\ResultReturnTrait;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ResultAissist extends BaseResult implements \ArrayAccess {

    use ResultReturnTrait;
    use ResultErrorTrait;
    use ResultArrayAccessTrait;

    private ResponseInterface $response;
    private ?string $text = null;

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function setResponse(ResponseInterface $response): self
    {
        $this->response = $response;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;

        return $this;
    }

    /****************************************************************************/

    public function getStatusCode(mixed $default = null): mixed {
        return $this->getResponse()?->getStatusCode() ?? $default;
    }

}
