{capture name="specialTaskLink"}<li><a href="#sniffer">Codesniffer</a></li>{/capture}
{capture name="specialTaskPane"}
{if !isset($project_phpcsFullReport)}
      <div id="sniffer">
No code sniffing metrics were collected in this build. If you haven't enabled
this yet, please add a PHPCodeSniffer task to this project's integration
builder, and configure it properly. If you already have this task enabled,
please check the raw output of this build for problems, such as a PHP Fatal error.
{else}
      <div id="sniffer">
        <div class="log">{$project_phpcsFullReport}</div>
{/if}
      </div>
{/capture}