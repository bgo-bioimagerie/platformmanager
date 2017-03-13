<style type="text/css">
<!--
table { vertical-align: top; }
tr    { vertical-align: top; }
td    { vertical-align: top; }
-->
</style>
<page backcolor="#FEFEFE" backimg="data/invoices/bas_page.png" backimgx="center" backimgy="bottom" backimgw="100%" backtop="0" backbottom="30mm" footer="date;heure;page" style="font-size: 12pt">
    <bookmark title="Lettre" level="0" ></bookmark>
    <table cellspacing="0" style="width: 100%; text-align: center; font-size: 14px">
        <tr>
            <td style="width: 25%;">
                <img style="width: 100%;" src="data/invoices/h2p2.gif" alt="Logo"><br>
            </td>
            <td style="width: 75%; color: #444444;">
                DEVIS
            </td>
        </tr>
    </table>
    <br>
    <br>
    <table cellspacing="0" style="width: 100%; text-align: left; font-size: 11pt;">
        <tr>
            <td style="width:60%;"></td>
            <td style="width:40%; ">
                <?php echo $resp ?> <br>
                <?php echo $adress ?> <br>
            </td>
        </tr>
    </table>
    <br>
    <br>
    <table cellspacing="0" style="width: 100%; text-align: left;font-size: 10pt">
        <tr>
            <td style="width:60%;"></td>
            <td style="width:40%; ">Rennes, le <?php echo $date ?></td>
        </tr>
    </table>
    <br>
    <table cellspacing="0" style="width: 100%; text-align: left;font-size: 10pt">
        <tr>
            <td style="width:50%;">
                BIOSIT - UMS 3480 - US 018 <br/>	
                Université de Rennes 1	<br/>
                2 avenue du Pr.Léon Bernard<br/>	
                CS 34317	<br/>
                35043 Rennes cedex	<br/><br/>
            </td>
        </tr>
        <tr>   
            <td style="width:50%;">
                Personne à contacter :	<br/>
                <b>Alain FAUTREL	<br/>
                PF H2P2 - HISTOPATOLOGIE </b><br/>	
                Tél : 02.23.23.48.78<br/>
                
            </td>
            <td style="width:50%; ">
                <table border="1" style="border-collapse:collapse; text-align:center;">
                        
                <tr><td style="width:25%;">453</td><td style="width:25%;"> <?php echo date("Y", time()) ?>	</td><td style="width:25%;">12PL514-03</td></tr>	
                <tr><td>U.B</td><td>	Année</td><td>	CR	</td></tr>
                <tr><td colspan="3">(Référence à rappeler)	</td></tr>
                </table>
                
            </td>
        </tr>
        <tr>
          <td style="width:50%; ">  
              <br>
              <table border="1" style="border-collapse:collapse; text-align:center;">
                
            <tr><td style="width:40%;">Unité : 453</td><td style="width:40%;">CR : 12PL514-03</td></tr>
	    <tr><td style="width:40%;">Dest : R3</td><td style="width:40%;">Cpte crédité : 7062</td></tr>
            </table>
            </td>
        </tr>    
    </table>
    	
    <br>
    <?php echo $table ?>
    <table cellspacing="0" style="width: 100%; border: solid 1px black; background: #E7E7E7; text-align: center; font-size: 10pt;">
        <tr>
            <th style="width: 87%; text-align: right;">Total : </th>
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
            
            <tr>
                <td style="width:60%; color:red;">
                    ATTENTION, ceci n'est pas une facture, merci de bien vouloir 
                retourner ce document signé " Bon pour accord " accompagné 
d'un bon de commande à Géraldine Gourmil - Biosit - établi sur 
le montant HT si paiement sur crédits UR1 ou établi sur le 
Montant TTC si paiement sur autres crédits
                    
                </td>
                <td style="width:40%; border: 1px dashed #000;">
                    <b>Bon pour accord le :</b><br>
                    Cachet et signature :<br>
                </td>
            </tr>
        </table>
    </nobreak>
</page>
