<style type="text/css">
<!--
table { vertical-align: top; }
tr    { vertical-align: top; }
td    { vertical-align: top; }
-->
</style>
<page backcolor="#FEFEFE" backimg="data/invoices/bas_page_micropicell.png" backimgx="center" backimgy="bottom" backimgw="100%" backtop="0" backbottom="30mm" footer="date;heure;page" style="font-size: 12pt">
    <bookmark title="Lettre" level="0" ></bookmark>
        
    <table cellspacing="0" style="width: 100%; text-align: center; font-size: 14px">
        <tr>
            <td style="width: 33%;">
                <img style="width: 60px;" src="data/invoices/logo_capacite.jpg" alt="Logo"><br>
            </td>
            <td style="width: 33%;">
                <img style="width: 90%;" src="data/invoices/MicroPICEll.jpg" alt="Logo"><br>
            </td>
            <td style="width: 33%;">
                <img style="width: 80px;" src="data/invoices/logo_un.png" alt="Logo"><br>
            </td>
        </tr>
    </table>
    <table cellspacing="0" style="width: 100%; text-align: center; font-size: 14px">
        <tr>
            <td style="width: 100%; color: #444444;">
                PLATE-FORME MICRO PICELL
            </td>
        </tr>
    </table>    
    <br>
    <br>
    <table cellspacing="0" style="width: 100%; text-align: left;font-size: 10pt;">
        <tr>
            <td style="width:35%; text-align: center; border: 2px solid #000;">
                <b>RELEVÉ</b>	<br/><br/>
            </td>
            <td style="width:15%;"></td>
            <td style="width:35%; ">Nantes, le <?php echo $date ?></td>
        </tr>
    </table>    
    <br>
    <table cellspacing="0" style="width: 100%; text-align: left;font-size: 10pt;">
        <tr>
            <td style="width:35%; border: 1px solid #000;">
                <b>A l'attention de :</b> <br/>	
                <b>CAPACITÉS SAS</b>	<br/>
                26 bd Vincent Gâche 44 200 NANTES<br/>	
                <i>Tel : 02 72 64 88 83</i>	<br/>
                <i>Fax : 02 72 64 88 98</i>	<br/>
                <i>E-mail : nathalie.moreau@capacites.fr</i>	<br/><br/>
                
            </td>
            <td style="width:15%;"></td>
            <td style="width:35%; ">RELEVÉ N° : <?php echo $number ?>
            <?php if ($invoiceInfo["title"] != ""){
                echo "<br>";
                echo "SUJET: " . $invoiceInfo["title"];
                
            }
            ?>
            <br><br>
            <?php echo $resp ?> <br>
                <?php echo $unit ?> <br><br>
                <?php echo $adress ?> <br>
            </td>
        </tr>
    </table>     
    <br>
    <table cellspacing="0" style="width: 100%; text-align: left;font-size: 10pt;">
        <tr>
            <td style="width:20%;"><i>Émetteur du relevé :</i>	</td>
            <td style="width:15%;"><i>Myriam Robard</i></td>
        </tr>
        <tr>
            <td style="width:20%;"></td>
            <td style="width:15%;"><i>06 75 61 81 71</i></td>
        </tr>
        <tr>
            <td style="width:20%;"></td>
            <td style="width:15%;"><i>myriam.robard@univ-nantes.fr</i></td>
        </tr>
    </table> 
    <br>
    <?php echo $table ?>
    <table cellspacing="0" style="width: 100%; border: solid 1px black; background: #E7E7E7; text-align: center; font-size: 10pt;">
        <tr>
            <th style="width: 87%; text-align: right;">Total <?php if($useTTC){echo "HT";} ?>: </th>
            <th style="width: 13%; text-align: right;"><?php echo number_format($total, 2, ',', ' '); ?> &euro;</th>
        </tr>
    </table>
    <?php if($useTTC){
        ?>
    
    <br>
    <table cellspacing="0" style="width: 100%; border: solid 1px black; background: #E7E7E7; text-align: center; font-size: 10pt;">
        <tr>
            <th style="width: 87%; text-align: right;">Total TTC: </th>
            <th style="width: 13%; text-align: right;"><?php echo number_format(1.2*$total, 2, ',', ' '); ?> &euro;</th>
        </tr>
    </table>
    <?php }
        ?>
    <br>
    <nobreak>
        <table cellspacing="0" style="width: 100%; text-align: left;">
            <tr style="color: #FF0000">
                <td colspan="2" style="width:100%; color:red;">
                A renvoyer par mail à CAPACITÉS - nathalie.moreau@capacites.fr
                </td>
            </tr>
            <tr>
                <td style="width:60%;">
                    <b>Paiement sur crédits :</b>
                </td>
                <td style="width:40%; height: 100px; border: 1px dashed #000;">
                    <b>Bon pour accord le :</b><br>
                    Cachet et signature :<br>
                </td>
            </tr>
        </table>
    </nobreak>
</page>
