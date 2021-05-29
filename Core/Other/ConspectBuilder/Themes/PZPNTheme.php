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

class PZPNTheme extends BaseTheme
{

    public function render(): int
    {
        $this->AddPage();
        $this->createDataTable();
        $this->createContent();

        $conspect = $this->conspectModel;

        $content = $this->Output("pzpn-themed-pdf", 'S');

        $file = File::create("pzpn-themed-pdf","pdf",$content,'conspect/'.$conspect->get("id"));

        $conspect->update([
            "pdf" => $file->getId()
        ]);

        return $file->getId();
    }

    public function createDataTable(){
        $this->SetTextColor(0);
        $this->SetY(5);
        $this->SetDrawColor(180, 180, 180);
        $this->SetLineWidth(0.1);
        $this->SetFontSize('10');

        $date = explode(' ',$this->conspectModel->date)[0];
        $time = explode(' ',$this->conspectModel->date)[1];
        $time = explode(':',$time)[0].":".explode(':',$time)[1];

        $tm = $this->conspectModel->time_min.'-'.$this->conspectModel->time_max.' min.';
        if($this->conspectModel->time_min===$this->conspectModel->time_max){
            $tm = $this->conspectModel->time_max." min.";
        }
        $pr = $this->conspectModel->player_min.'-'.$this->conspectModel->player_max.' os.';
        if($this->conspectModel->player_min===$this->conspectModel->player_max){
            $pr = $this->conspectModel->player_max." os.";
        }

        $tbl = '
            <table border="1" cellpadding="2" bordercolor="#b4b4b4" style="border-color:#b4b4b4; border: 1px solid #b4b4b4">
             <tr>
              <td width="60" align="center" style="background-color:#f1f1f1;">Data:</td>
              <td width="100" align="center">' .$date.'</td>
              <td width="70" align="center" style="background-color:#f1f1f1;">Godzina:</td>
              <td width="60" align="center">'.$time.'</td>
              <td width="60" align="center" style="background-color:#f1f1f1;">Czas:</td>
              <td width="80" align="center">'.$tm.'</td>
              <td width="120" align="center" style="background-color:#f1f1f1;">L. zawodników:</td>
              <td width="84" align="center">'.$pr.'</td>
             </tr>
             <tr>
              <td width="80" align="center" style="background-color:#f1f1f1;">Drużyna:</td>
              <td width="130" align="center">' .$this->conspectModel->team.'</td>
              <td width="70" align="center" style="background-color:#f1f1f1;">Obiekt:</td>
              <td width="135" align="center">'.$this->conspectModel->place.'</td>
              <td width="90" align="center" style="background-color:#f1f1f1;">Obciążenie:</td>
              <td width="129" align="center">'.$this->conspectModel->weight.'</td>
             </tr>
             <tr>
              <td width="150" align="center" style="background-color:#f1f1f1;">Sprzęt treningowy:</td>
              <td width="484" align="center">' .implode(", ",json_decode($this->conspectModel->equipment)).'</td>
             </tr>
             <tr>
              <td width="75" align="center" style="background-color:#f1f1f1;">Temat:</td>
              <td width="559" align="center">' .$this->conspectModel->title.'</td>
             </tr>
             <tr>
              <td width="75" align="center" style="background-color:#f1f1f1;">Trener:</td>
              <td width="260" align="center">' .$this->conspectModel->coach.'</td>
               <td width="75" align="center" style="background-color:#f1f1f1;">Sezon:</td>
              <td width="224" align="center">' .$this->conspectModel->season.'</td>
             </tr>
            </table>';


        $html = '<table width="634" align="center">
            <tr>
                <td width="100"><br/><br/>';

        if( $this->conspectModel->img ){

            $dirToImg = File::getById((int)$this->conspectModel->img);
            $dirToImg = $dirToImg->getPath();

            $html .= '<img style="height:100px" src="'.$dirToImg.'">';
        }

        $html .= '</td>
                <td width="434">
                    <h3 style="text-transform: uppercase;">'.$this->conspectModel->title.'</h3>
                    <p style="font-size: 14px; color: #545B6F;">'.$this->conspectModel->description.'</p>
                </td>
                <td width="100"><br/><br/>
                    <img style="height:100px" src="./resources/cmc.png">
                </td>
            </tr>
            </table>';

        $this->writeHTML($html, true, false, false, false, '');
        $this->Ln(3);
        $this->writeHTML($tbl, true, false, false, false, '');

    }

    public function createContent(){

        $html = "<table border=\"1\" cellpadding=\"2\" width=\"634\" align=\"center\" valign=\"middle\" style=\"border-bottom: 1px solid #ececec;\">
                <tr align=\"center\" valign=\"middle\">
                    <td width=\"90\" style=\"background-color:#f1f1f1;\" align=\"center\" valign=\"middle\">CZĘŚĆ TRENINGU</td>
                    <td width=\"70\" style=\"background-color:#f1f1f1;\" align=\"center\" valign=\"middle\">CZAS</td>
                    <td width=\"324\" style=\"background-color:#f1f1f1;\" align=\"center\" valign=\"middle\">TREŚĆ TRENINGU</td>
                    <td width=\"150\" style=\"background-color:#f1f1f1;\" align=\"center\" valign=\"middle\">WSKAZÓWKI</td>
                </tr>";

        foreach( json_decode($this->conspectModel->conspect_elements) as $mainKey => $part ){
            $name = $part->name;

            $html .= "<tr>
                <td style=\"font-size: 12px; color: #545B6F; text-align: center; width: 90px\" nobr=\"true\" valign=\"middle\">".($this->getWordlPerIndex($mainKey)).". ".$name."</td>
                <td style=\"width: 544px\" colspan=\"3\"><table >";

            if(isset($part->elements)){
                $elCount = count($part->elements);
                foreach ( $part->elements as $key => $element ){
                    $elTime = $element->time[0]."-".$element->time[1]." min.";
                    if($element->time[0]===$element->time[1]){
                        $elTime = $element->time[1]." min.";
                    }
                    $html .= "<tr nobr=\"true\" >";

                    $html .= "<td style=\"width:68px; border-right: 1px solid #cbcbcb;border-bottom: 1px solid #cbcbcb\">
                                <table>
                                    <tr>
                                        <td style=\"color: #545B6F; font-size: 9px; line-height: 12px;text-align: center;\">".$elTime."</td>
                                    </tr>
                                </table>
                            </td>";

                       if( $element->id == "-1" ){
                           $html .= "<td nobr=\"true\" style=\"width:324px; text-align: left;border-bottom: 1px solid #cbcbcb\">
                                       <table>
                                       <tr>
                                           <td style=\"width:10px\"></td>
                                           <td style=\"font-size: 12px; line-height: 12px; width:304px\">".($this->getWordlPerIndex($mainKey)).".".($key+1).". ".$element->name."</td>
                                           <td style=\"width:10px\"></td>
                                       </tr>
                                       <tr>
                                           <td style=\"width:10px\"></td>
                                           <td style=\"color: #393E4D; font-size: 12px; width:304px\"></td>
                                           <td style=\"width:10px\"></td>
                                       </tr>
                                       </table>
                                       </td>";
                           $html .= "<td style=\"width:148px; border-left: 1px solid #cbcbcb;border-bottom: 1px solid #cbcbcb\">
                                <table>
                                    <tr>
                                        <td style=\"color: #545B6F; font-size: 9px; line-height: 12px;text-align: left;\">".(strlen($element->description)>0?nl2br($element->description):'')."</td>
                                    </tr>
                                </table>
                            </td></tr>";
                       }else{
                           $animationElement = Animation::find($element->id);

                           $img = File::getById((int)$animationElement->main_image);
                           $img = $img->getPath();

                           $html .= "<td nobr=\"true\" style=\"width:324px; text-align: left;border-bottom: 1px solid #cbcbcb;\">
                                   <table>
                                   <tr>
                                       <td style=\"width:10px\"></td>
                                       <td style=\"font-size: 12px; line-height: 12px; width:304px\">".($this->getWordlPerIndex($mainKey)).".".($key+1).". ".$animationElement->name."</td>
                                       <td style=\"width:10px\"></td>
                                   </tr>
                                   <tr>
                                       <td style=\"width:10px\"></td>
                                       <td style=\"color: #393E4D; font-size: 12px; width:304px\"></td>
                                       <td style=\"width:10px\"></td>
                                   </tr>
                                   <tr>
                                       <td style=\"width:10px\"></td>
                                       <td style=\"font-size: 12px; line-height: 12px; width:294px\"><img   style=\"width:304px; height: auto;\" src=\"".$img."\" width=\"294\"/></td>
                                       <td style=\"width:10px\"></td>
                                   </tr>
                                   <tr>
                                       <td style=\"width:10px\"></td>
                                       <td style=\"color: #393E4D; font-size: 12px; width:304px\">Organizacja i przebieg :</td>
                                       <td style=\"width:10px\"></td>
                                   </tr>
                                   <tr>
                                       <td style=\"width:10px\"></td>
                                       <td style=\"color: #545B6F; font-size: 10px; width:304px\">".nl2br($animationElement->description)."</td>
                                       <td style=\"width:10px\"></td>
                                   </tr>
                                   <tr>
                                       <td style=\"width:10px\"></td>
                                       <td style=\"color: #393E4D; font-size: 12px; width:304px\"></td>
                                       <td style=\"width:10px\"></td>
                                   </tr>
                                   </table>
                                   </td>";
                           $html .= "<td style=\"width:148px; border-left: 1px solid #cbcbcb;border-bottom: 1px solid #cbcbcb\">
                                <table>
                                    <tr>
                                        <td style=\"color: #545B6F; font-size: 9px; line-height: 12px;text-align: left;\">".(strlen($animationElement->tips)>0?nl2br($animationElement->tips):'')."</td>
                                    </tr>
                                </table>
                            </td></tr>";
                       }
                }
            }

            $html .="</table></td></tr>";
        }

        $html .= "</table>";
        $this->writeHTML($html, true, false, true, false, '');
        $this->lastPage();
    }

    public function getWordlPerIndex( $index ){
        return ("ABCDEFGHIJKLMNOPQRSTUVWXYZ")[$index];
    }

    public function Header(){
        $this->SetY(16);
        $this->SetFont('lato', '', 10);
        if($this->getPage() > 1){
            $this->writeHTML('
            <table border="1" cellpadding="2" width="634" align="center" valign="middle" style="border-bottom: 1px solid #ececec;">
                <tr align="center" valign="middle">
                    <td width="90" style="background-color:#f1f1f1;" align="center" valign="middle">CZĘŚĆ TRENINGU</td>
                    <td width="70" style="background-color:#f1f1f1;" align="center" valign="middle">CZAS</td>
                    <td width="324" style="background-color:#f1f1f1;" align="center" valign="middle">TREŚĆ TRENINGU</td>
                    <td width="150" style="background-color:#f1f1f1;" align="center" valign="middle">WSKAZÓWKI</td>
                </tr>
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