<table style="margin: 1px;" class="outer">
    <tr>
        <th colspan="3"><a href="<{$channel.link}>" target="_blank"><{$channel.title}></a></th>
    </tr>
    <tr>
        <td style="width: 25%;" rowspan="6">
            <{if $image.url != ""}>
                <img src="<{$image.url}>" width="<{$image.width|default:88}>" height="<{$image.height|default:31}>"
                     alt="<{$image.title}>">
            <{else}>
                &nbsp;
            <{/if}>
        </td>
        <td style="vertical-align: top;" class="head"><{$lang_lastbuild}></td>
        <td class="odd"><{$channel.lastbuilddate|default:"&nbsp;"}></td>
    </tr>
    <tr>
        <td style="vertical-align: top;" class="head"><{$lang_description}></td>
        <td class="even"><{$channel.description|default:"&nbsp;"}></td>
    </tr>
    <tr>
        <td style="vertical-align: top;" class="head"><{$lang_webmaster}></td>
        <td class="odd"><{$channel.webmaster|default:"&nbsp;"}></td>
    </tr>
    <tr>
        <td style="vertical-align: top;" class="head"><{$lang_category}></td>
        <td class="even"><{$channel.category|default:"&nbsp;"}></td>
    </tr>
    <tr>
        <td style="vertical-align: top;" class="head"><{$lang_generator}></td>
        <td class="odd"><{$channel.generator|default:"&nbsp;"}></td>
    </tr>
    <tr>
        <td style="vertical-align: top;" class="head"><{$lang_language}></td>
        <td class="even"><{$channel.language|default:"&nbsp;"}></td>
    </tr>
    <{section name=i loop=$items}>
        <tr class="head">
            <td style="padding-left: 2em;" colspan="3">
                <{if $items[i].title != ""}><a id="<{$items[i].link}>"></a>
                    <a href="<{$items[i].link}>" target="_blank"><{$items[i].title}></a>
                <{/if}>
            </td>
        </tr>
        <{if $show_full == true}>
            <{if $items[i].category != ""}>
                <tr>
                    <td class="even" style="vertical-align: top;"><{$lang_category}></td>
                    <td class="odd" colspan="2"><{$items[i].category}>
                </tr>
            <{/if}>
            <{if $items[i].pubdate != ""}>
                <tr>
                    <td class="even" style="vertical-align: top;"><{$lang_pubdate}>:</td>
                    <td class="odd" colspan="2"><{$items[i].pubdate}></td>
                </tr>
            <{/if}>
            <{if $items[i].description != ""}>
                <tr>
                    <td class="even" style="vertical-align: top;"><{$lang_description}>:</td>
                    <td colspan="2" class="odd"><{$items[i].description}><{if $items[i].guid != ""}>&nbsp;&nbsp;<a
                            href="<{$items[i].guid}>" target="_blank"><{$lang_more}></a><{/if}></td>
                </tr>
            <{elseif $items[i].guid != ""}>
                <tr>
                    <td class="even" style="vertical-align: top;"></td>
                    <td colspan="2" class="odd"><a href="<{$items[i].guid}>" target="_blank"><{$lang_more}></a></td>
                </tr>
            <{/if}>
        <{/if}>
    <{/section}>
</table>
