<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 21.06.18
 * Time: 21:54
 */

namespace Core\Other\ConspectBuilder\Contract;

use Core\Model\Conspect;
use Core\Other\ConspectBuilder\Contract\IConspectTheme;
use TCPDF;

abstract class BaseTheme extends TCPDF implements IConspectTheme
{
    protected $pdf;
    /**
     * @var Conspect
     */
    protected $conspectModel;
    /**
     * @var string
     */
    protected $userUniqueToken;

    public abstract function render(): int;
    public function init(Conspect $conspectModel, string $userUniqueToken){
        $this->conspectModel = $conspectModel;
        $this->userUniqueToken = $userUniqueToken;

        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor('Club Manager Center');
        $this->SetTitle('Konspekt');
        $this->SetSubject('Konspekt');
        $this->SetKeywords('Konspekt');

        $this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $this->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);

        $this->SetFont("lato");
    }

}