<div id="help-template" class="outer">
    <h1 class="head">Help:
        <a class="ui-corner-all tooltip" href="<{$xoops_url}>/modules/<{$smarty.const._MI_WFL_DIRNAME}>/admin/index.php"
           title="Back to the administration of <{$smarty.const._MI_WFL_NAME}>"> <{$smarty.const._MI_WFL_NAME}>
            <img src="<{xoAdminIcons home.png}>"
                 alt="Back to the Administration of <{$smarty.const._MI_WFL_NAME}>">
        </a></h1>
    <TITLE>Installation</TITLE>


    <TABLE cellpadding="5">
        <td width="600" style="background-color:#FF0000; font-weight: bold;"><p><font size="-1"><b><font color="#FFFFFF"
                                                                                                         face="Verdana, Arial, Helvetica, sans-serif">Deze
            instrukties zijn voor een nieuwe installatie.</font></b>
        </font></p>
            <p><font color="#FFFFFF" size="-1" face="Verdana, Arial, Helvetica, sans-serif">Als u wilt converteren van
                Xoops myLinks kies dan 'Converteren' uit het menu.</font></p></TD>
    </TABLE>

    <p><font size="-1" face="Verdana, Arial, Helvetica, sans-serif"><strong>Opmerking:</strong> Het is altijd goed om
        eerst een database backup te maken alvorens een module te installeren.<br>
        <br>
    </font></p>
    <p><font size="+2" face="Verdana, Arial, Helvetica, sans-serif"><strong><u>Nieuwe installatie van
        WF-Links</u></strong> </font></p>
    <ol>
        <li><font size="-1" face="Verdana, Arial, Helvetica, sans-serif"><u><strong>Uploaden van de module naar uw
            website</strong></u><br>
            <br>
            Upload de mappen <em>'modules/wflinks'</em> en <em>'uploads/images'</em> naar uw
            <em>{xoops-rootdirectory}<br>
                <br>
            </em>Indien u de map <em>'modules/wflinks'</em> wilt veranderen in bijv. <em>'modules/weblinken'</em>, dan
            dient u dat eerst te doen, alvorens verder te gaan met de installatie.</font><font size="-1"
                                                                                               face="Verdana, Arial, Helvetica, sans-serif"><br>
            <br>
        </font></li>
        <li><font size="-1" face="Verdana, Arial, Helvetica, sans-serif"><strong><u>Verander en controlleer de map
            rechten</u></strong><br>
            <br>
            CHMOD de volgende mappen 777:<br>
            <br>
            <em>{xoops-rootdirectory}/uploads/images<br>
                {xoops-rootdirectory}/uploads/images/category<br>
                {xoops-rootdirectory}/uploads/images/category/thumbs<br>
                {xoops-rootdirectory}/uploads/images/flags<br>
                {xoops-rootdirectory}/uploads/images/flags/flags_small<br>
                {xoops-rootdirectory}/uploads/images/screenshots<br>
                {xoops-rootdirectory}/uploads/images/screenshots/thumbs<br>
                {xoops-rootdirectory}/uploads/images/thumbs<br>
                <br>
            </em></font></li>
        <li><font size="-1" face="Verdana, Arial, Helvetica, sans-serif"><strong><u>Installeer de
            module</u></strong><br>
            <br>
            Login als administrator, ga naar de Administratie pagina, kies <em>Systeem --&gt; modules</em> en installeer
            WF-Links.<br>
            <br>
        </font></li>
        <li><font size="-1" face="Verdana, Arial, Helvetica, sans-serif"><strong><u>Configureer de
            module</u></strong><br>
            <br>
            De meest belangrijke stap nu is om de groep- en blokrechten voor de module in te stellen via <em>Systeem --&gt;
                Groepen</em></font><br>
            <br>
        </li>
    </ol>
    <!-- -----Help Content ---------- -->

</div>
