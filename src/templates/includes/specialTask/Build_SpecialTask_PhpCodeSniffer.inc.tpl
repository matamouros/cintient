{capture name="specialTaskLink"}<a href="#" class="sniffer">sniffer</a>{/capture}
{capture name="specialTaskPane"}
{if !isset($project_phpcsFullReport)}
      <div id="sniffer" class="buildResultPane">
No code sniffing metrics were collected in this build. If you haven't enabled
this yet, please add a PHPCodeSniffer task to this project's integration
builder, and configure it properly. If you already have this task enabled,
please check the raw output of this build for problems, such as a PHP Fatal error.
{else}
      <div id="sniffer" class="buildResultPane rawText">
        {$project_phpcsFullReport}
{/if}
      </div>
{/capture}