<{if $lcr=='l'}><{assign var=lcr value='left'}>
<{elseif $lcr=='r'}><{assign var=lcr value='right'}>
<{else}><{assign var=lcr value='center'}>
<{/if}>

<{assign var=zone value="page_$topbottom$lcr"}>

<{*
<{if $xoBlocks[$zone]}>
<div class="xo-blockszone x2-secondary" id="xo-page-<{$topbottom}><{$lcr}>blocks">
    <{foreach from=$xoBlocks[$zone] item=block}>
    <div class="blockbg">
        <div class="xo-block <{$block.module}>">
            <{if $block.title}>
            <div class="xo-blocktitle"><{$block.title}></div>
            <{/if}>
            <div class="xo-blockcontent"><{$block.content}></div>
        </div>
    </div>
    <{/foreach}>
</div>
<{/if}>
*}>

<{if $xoBlocks[$zone]}>
    <div class="xo-blockszone x2-secondary" id="xo-page-<{$topbottom}><{$lcr}>blocks">
        <{foreach from=$xoBlocks[$zone] item=block}>
            <div class="xo-block <{$block.module}>">
                <{if $block.title}>
                    <div class="xo-blocktitle"><{$block.title}></div>
                <{/if}>
                <div class="xo-blockcontent"><{$block.content}></div>
            </div>
        <{/foreach}>
    </div>
<{/if}>
