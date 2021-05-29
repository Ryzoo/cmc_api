<?php
/**
 * Created by PhpStorm.
 * User: ryzo
 * Date: 21.06.18
 * Time: 21:17
 */

namespace Core\Other\ConspectBuilder\Themes;
use Core\Model\Animation;
use Core\Other\ConspectBuilder\Contract\BaseTheme;
use Core\System\File;
use Core\System\FileManager;

class CMCTheme extends BaseTheme
{

    public function render(): int
    {
        $this->titlePage();
        $this->mainContent();

        $conspect = $this->conspectModel;

        $content = $this->Output("pzpn-themed-pdf", 'S');

        $file = File::create("pzpn-themed-pdf","pdf",$content,'conspect/'.$conspect->get("id"));

        $conspect->update([
            "pdf" => $file->getId()
        ]);

        return $file->getId();
    }

    public function titlePage(){
        $this->AddPage();
        $tm = $this->conspectModel->get("time_min").'-'.$this->conspectModel->get("time_max").' min.';
        if($this->conspectModel->get("time_min")===$this->conspectModel->get("time_max")){
            $tm = $this->conspectModel->get("time_max")." min.";
        }
        $pr = $this->conspectModel->get("player_min").'-'.$this->conspectModel->get("player_max").' osób.';
        if($this->conspectModel->get("player_min")===$this->conspectModel->get("player_max")){
            $tm = $this->conspectModel->get("player_max")." osób.";
        }

        $html = '
        <table style="width: 100%; text-align: center">
            <tr><td colspan="4"></td></tr>
            <tr>
                <td colspan="4"><h2 style="text-transform: uppercase;">'.$this->conspectModel->get("title").'</h2></td>
            </tr>
            <tr><td colspan="4"></td></tr>
            <tr><td colspan="4"></td></tr>
            <tr><td colspan="4"></td></tr>
            <tr>
                <td></td>
                <td colspan="2"><p style="font-size: 14px; color: #545B6F;">'.$this->conspectModel->get("description").'</p></td>
            </tr>
            <tr><td colspan="4"></td></tr>
            <tr><td colspan="4"></td></tr>
            <tr><td colspan="4"></td></tr>
            <tr><td colspan="4"></td></tr>
            <tr><td colspan="4"></td></tr>
            <tr>
                <td style="text-align: right">Sezon:</td>   
                <td colspan="3" style="font-size: 12px; color: #545B6F; text-align: left; line-height: 21px">'.$this->conspectModel->season.'</td>   
            </tr>
            <tr>
                <td style="text-align: right">Data:</td>   
                <td colspan="3" style="font-size: 12px; color: #545B6F; text-align: left; line-height: 21px">'.$this->conspectModel->date.'</td>   
            </tr>
            <tr>
                <td style="text-align: right">Czas trwania:</td>   
                <td colspan="3" style="font-size: 12px; color: #545B6F; text-align: left; line-height: 21px">'.$tm.'</td>   
            </tr>
            <tr>
                <td style="text-align: right">Liczba zawodników:</td>   
                <td colspan="3" style="font-size: 12px; color: #545B6F; text-align: left; line-height: 21px">'.$pr.'</td>   
            </tr>
            <tr>
                <td style="text-align: right">Drużyna:</td>   
                <td colspan="3" style="font-size: 12px; color: #545B6F; text-align: left; line-height: 21px">'.$this->conspectModel->team.'</td>   
            </tr>
            <tr>
                <td style="text-align: right">Obiekt:</td>   
                <td colspan="3" style="font-size: 12px; color: #545B6F; text-align: left; line-height: 21px">'.$this->conspectModel->place.'</td>   
            </tr>
            <tr>
                <td style="text-align: right">Obciążenie:</td>   
                <td colspan="3" style="font-size: 12px; color: #545B6F; text-align: left; line-height: 21px">'.$this->conspectModel->weight.'</td>   
            </tr>
            <tr>
                <td style="text-align: right">Sprzęt treningowy:</td>   
                <td colspan="3" style="font-size: 12px; color: #545B6F; text-align: left; line-height: 21px">'.implode(", ",json_decode($this->conspectModel->equipment)).'</td>   
            </tr>
            <tr>
                <td style="text-align: right">Temat:</td>   
                <td colspan="3" style="font-size: 12px; color: #545B6F; text-align: left; line-height: 21px">'.$this->conspectModel->title.'</td>   
            </tr>
            <tr>
                <td style="text-align: right">Trener prowadzący:</td>   
                <td colspan="3" style="font-size: 12px; color: #545B6F; text-align: left; line-height: 21px">'.$this->conspectModel->coach.'</td>   
            </tr>
            <tr><td colspan="4"></td></tr>
            <tr><td colspan="4"></td></tr>
            <tr><td colspan="4"></td></tr>
            <tr><td colspan="4"></td></tr>';

        if( $this->conspectModel->img ){

            $dirToImg = File::getById((int)$this->conspectModel->img);
            $dirToImg = $dirToImg->getPath();

            $html .= '<tr>
                        <td colspan="2">
                            <img style="height:100px" src="'.$dirToImg.'">
                        </td>
                        <td colspan="2">
                            <img style="height:100px" src="./resources/cmc.png">
                        </td>
                    </tr>';
        }else{
            $html .= '<tr>
                        <td colspan="4">
                            <img style="height:100px" src="./resources/cmc.png">
                        </td>
                    </tr>';
        }

        $html .= '</table>';

        $this->writeHTML($html, true, false, true, false, '');
        $this->lastPage();
    }

    public function mainContent(){
        $this->AddPage();
        $html = "<table style=\"\">";

        foreach( json_decode($this->conspectModel->conspect_elements) as $mainKey => $part ){
            $time = $part->time[0]."-".$part->time[1]." min.";
            if($part->time[0]===$part->time[1]){
                $time = $part->time[1]." min.";
            }
            $name = $part->name;
            $html .= "<tr>
                <td style=\"font-size: 12px; color: #545B6F; text-align: right;width:90px\" nobr=\"true\"><small>".$time."</small><br/>".$this->getWordlPerIndex($mainKey).". ".$name."</td>
                <td colspan=\"2\"><table>";

            if(isset($part->elements)){
                $elCount = count($part->elements);
                foreach ( $part->elements as $key => $element ){
                    $elTime = $element->time[0]."-".$element->time[1]." min.";
                    if($element->time[0]===$element->time[1]){
                        $elTime = $element->time[1]." min.";
                    }
                    $html .= "<tr nobr=\"true\">";

                    if( $element->id == "-1" ){
                        $html .= "<td nobr=\"true\" style=\"width:454px; text-align: left;\">
                                    <table>
                                        <tr>
                                            <td style=\"width:10px\"></td>
                                            <td style=\"font-size: 12px; line-height: 12px; width:434px\">".($this->getWordlPerIndex($mainKey)).".".($key+1).". ".$element->name."</td>
                                            <td style=\"width:10px\"></td>
                                        </tr>
                                        <tr>
                                            <td style=\"width:10px\"></td>
                                            <td style=\"color: #393E4D; font-size: 12px; width:434px\">Wskazówki:</td>
                                            <td style=\"width:10px\"></td>
                                        </tr>
                                        <tr>
                                            <td style=\"width:10px\"></td>
                                            <td style=\"color: #545B6F; font-size: 10px; width:434px\">".(strlen($element->description)>0?$element->description:'----')."</td>
                                            <td style=\"width:10px\"></td>
                                        </tr>
                                    </table>
                                </td>";
                    }else{
                        $animationElement = Animation::find($element->id);

                        $img = File::getById((int)$animationElement->main_image);
                        $img = $img->getPath();

                        $elTime = $element->time[0]."-".$element->time[1]." min.";
                        if($element->time[0]===$element->time[1]){
                            $elTime = $element->time[1]." min.";
                        }
                        $html .= "<td nobr=\"true\" style=\"width:454px; text-align: left;\">
                                    <table>
                                        <tr>
                                            <td style=\"width:10px\"></td>
                                            <td style=\"font-size: 12px; line-height: 12px; width:434px\">".($this->getWordlPerIndex($mainKey)).".".($key+1).". ".$animationElement->name."</td>
                                            <td style=\"width:10px\"></td>
                                        </tr>
                                        <tr>
                                            <td style=\"width:10px\"></td>
                                            <td style=\"color: #393E4D; font-size: 12px; width:434px\">Organizacja i przebieg :</td>
                                            <td style=\"width:10px\"></td>
                                        </tr>
                                        <tr>
                                            <td style=\"width:10px\"></td>
                                            <td style=\"color: #545B6F; font-size: 10px; width:434px\">".$animationElement->description."</td>
                                            <td style=\"width:10px\"></td>
                                        </tr>
                                        <tr>
                                            <td style=\"width:10px\"></td>
                                            <td ><img style=\"width:434px; height: auto;\" src=\"".$img."\" width=\"434px\"/></td>
                                            <td style=\"width:10px\"></td>
                                        </tr>
                                        <tr>
                                            <td style=\"width:10px\"></td>
                                            <td style=\"color: #393E4D; font-size: 12px; width:434px\">Wskazówki:</td>
                                            <td style=\"width:10px\"></td>
                                        </tr>
                                        <tr>
                                            <td style=\"width:10px\"></td>
                                            <td style=\"color: #545B6F; font-size: 10px; width:434px\">".(strlen($animationElement->tips)>0?$animationElement->tips:'----')."</td>
                                            <td style=\"width:10px\"></td>
                                        </tr>
                                    </table>
                                </td>";
                    }

                    $html .= "<td style=\"width:90px;\">
                                <table>
                                    <tr>
                                        <td style=\"color: #545B6F; font-size: 9px; line-height: 12px\">".$elTime."</td>
                                    </tr>
                                </table>
                            </td></tr>";

                    if( $key != $elCount-1 )
                    $html .= "<tr><td ></td></tr>";

                }
            }

            $html .="</table></td></tr>";
            $html .="<tr><td colspan=\"4\"></td></tr>";
            $html .="<tr><td colspan=\"4\" style=\"background-color: #F6F6F6; height: 1px !important; line-height: 1px; width: 100%;\"></td></tr>";
            $html .="<tr><td colspan=\"4\"></td></tr>";
        }

        $html .= "</table>";
        $this->writeHTML($html);
        $this->lastPage();
    }

    public function getWordlPerIndex( $index ){
        return ("ABCDEFGHIJKLMNOPQRSTUVWXYZ")[$index];
    }

    public function Header(){
        $this->SetY(10);
        $this->SetFont('lato', '', 12);
        if($this->getPage() > 1){
            $this->writeHTML('
            <table style="border-bottom: 1px solid #ececec; ">
                <tr style="text-align: center;">
                    <th style="width:90px; text-align: right;">Część</th>
                    <th style="width:454px">Treść treningu</th>
                    <th style="width:90px; text-align: left;">Czas trwania</th>
                </tr>
                <tr><td></td></tr>
            </table>
        ');
        }
    }

    public function Footer()
    {
        $this->SetY(-7);
        $this->SetFont('lato', '', 9);
        $actualPage = 'Strona: '.$this->getPage() .' / '.$this->getAliasNbPages();
        $this->writeHTML('
            <table>
                <tr style="text-align: center; color: #838383; font-size: 8px;">
                    <td ></td>
                    <td style="text-align:center;">'.$this->conspectModel->coach.'</td>
                    <td style="text-align:center;">|</td>
                    <td style="text-align:center;">'.$actualPage.'</td>
                    <td style="text-align:center;">|</td>
                    <td style="text-align:center;">CENTRUKLUBU.PL</td>
                    <td ></td>
                </tr>
            </table>
        ');
    }

}