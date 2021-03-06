<div id="help-template" class="outer">
    <h1 class="head">Help:
        <a class="ui-corner-all tooltip" href="<{$xoops_url}>/modules/<{$smarty.const._MI_WFL_DIRNAME}>/admin/index.php"
           title="Back to the administration of <{$smarty.const._MI_WFL_NAME}>"> <{$smarty.const._MI_WFL_NAME}>
            <img src="<{xoAdminIcons home.png}>"
                 alt="Back to the Administration of <{$smarty.const._MI_WFL_NAME}>">
        </a></h1>
    <TITLE>Convert</TITLE>


    <TABLE cellpadding="5">
        <td width="550" style="background-color:#FF0000; font-weight: bold;"><b><font color="#FFFFFF" size="-1"
                                                                                      face="Verdana, Arial, Helvetica, sans-serif">Instrukties
            voor het converteren van myLinks/webLinks naar WF-Links.</font></b>
            <P><font color="#FFFFFF" size="-1" face="Verdana, Arial, Helvetica, sans-serif">Indien u een nieuwe
                installatie wilt doen kies dan 'Installeren' uit het menu.</font></TD>
    </TABLE>
    <p><font size="-1" face="Verdana, Arial, Helvetica, sans-serif"><strong>Opmerking:</strong> Het is altijd goed om
        eerst een database backup te maken alvorens een module te installeren.</font>
    <p>

    <p><FONT FACE="Verdana, Arial, Helvetica, sans-serif" SIZE="+2"><U><strong>Conversie van Xoops myLinks/webLinks ==>
        WF-Links</strong></U></FONT>
    <p><font size="-1" face="Verdana, Arial, Helvetica, sans-serif">

        <br>
        Opmerking: Wanneer u de conversie doet zal het update script de myLinks tabellen in de database in WF-Links
        tabellen omzetten. Na de conversie kunt u myLinks/webLinks niet meer gebruiken, omdat de tabellen ontbreken. Als
        u myLinks of webLinks werkend wilt houden dient u eerst een backup van de myLinks/webLinks tabellen te maken en
        ze na de conversie weer terug te plaatsen. Het is mogelijk om WF-Links en myLinks/webLinks naast elkaar te laten
        werken (hoewel wij niet weten waarom u dat zou willen).</font>
        <br>
        <br>
        <ol>
            <li><U><b><font size="-1" face="Verdana, Arial, Helvetica, sans-serif">Maak een backup</font></b></U>
    <P><font size="-1" face="Verdana, Arial, Helvetica, sans-serif">Maak een backup van de myLinks/webLinks tabellen van
        uw database.</font>
        </li>
        <li><U><b><font size="-1" face="Verdana, Arial, Helvetica, sans-serif">Upload de module naar uw
            website</font></b></U><font face="Arial">
    <P><font size="-1" face="Verdana, Arial, Helvetica, sans-serif">Upload de '<I>modules/wflinks</I>' en
        '<I>uploads</I>' map naar uw <I>{xoops-rootdirectory}<br>
            <br>
        </I>Indien u de map <em>'modules/wflinks'</em> wilt veranderen in bijv. <em>'modules/weblinken'</em>, dan dient
        u dat eerst te doen, alvorens verder te gaan met de installatie.</font>    </li>
        <li><U><b><font size="-1" face="Verdana, Arial, Helvetica, sans-serif">Veranderen en controlleren van de map
            rechten</font></b></U>
    <P><font size="-1" face="Verdana, Arial, Helvetica, sans-serif">CHMOD de volgende mappen 777:
    </font>
    <p><font size="-1" face="Verdana, Arial, Helvetica, sans-serif"><em>{xoops-rootdirectory}/uploads/images<br>
        {xoops-rootdirectory}/uploads/images/category<br>
        {xoops-rootdirectory}/uploads/images/category/thumbs<br>
        {xoops-rootdirectory}/uploads/images/flags<br>
        {xoops-rootdirectory}/uploads/images/flags/flags_small<br>
        {xoops-rootdirectory}/uploads/images/screenshots<br>
        {xoops-rootdirectory}/uploads/images/screenshots/thumbs<br>
        {xoops-rootdirectory}/uploads/images/thumbs</em></font></li>
        <li><font size="-1" face="Verdana, Arial, Helvetica, sans-serif"><U><b>Installeer de module</b></U></font>
    <P><font size="-1" face="Verdana, Arial, Helvetica, sans-serif">Login als administrator, ga naar de Administratie
        pagina, kies <em>Systeem --&gt; modules</em> en installeer WF-Links.</font></li>
        <li><U><b><font size="-1" face="Verdana, Arial, Helvetica, sans-serif">Start het conversie script</font></b></U>
    <P><font size="-1" face="Verdana, Arial, Helvetica, sans-serif">Ga met uw browser naar <I>{xoops-rootdirectory}/modules/wflinks/update.php</I>
        en start het update script. <br>
        Volg de instrukties tijdens de installatie procedure. <br>
        Het script probeert de versie van myLinks of webLinks vast te stellen die u heeft geïnstalleerd en zal proberen
        het te updaten.</font>
        <li><U><b><font size="-1" face="Verdana, Arial, Helvetica, sans-serif">Update de module</font></b></U>
    <P><font size="-1" face="Verdana, Arial, Helvetica, sans-serif">Ga terug naar <i>Systeem --> Modules</i> en update
        WF-Links, dit is om de templates bij te werken.</font><br>
        </li>
        <li><U><b><font size="-1" face="Verdana, Arial, Helvetica, sans-serif">Configureer de module</font></b></U>
    <P><font size="-1" face="Verdana, Arial, Helvetica, sans-serif">De meest belangrijke stap nu is om de groep- en
        blokrechten voor de module in te stellen via <em>Systeem --&gt; Groepen</em></font></li>
        <li><U><b><font size="-1" face="Verdana, Arial, Helvetica, sans-serif">Herstel of verwijder
            myLinks/webLinks</font></b></U>
    <P><font size="-1" face="Verdana, Arial, Helvetica, sans-serif">Als u myLinks of webLinks wilt blijven gebruiken
        naast WF-Links, herstel dan uw myLinks/weblinks tabellen met de in stap 1 gemaakte backup.<br>
        Als myLinks of webLinks niet meer wilt gebruiken deactiveer en deïnstalleer de oude module dan.</font>
        </li>
        </ol>
        <!-- -----Help Content ---------- -->

</div>
