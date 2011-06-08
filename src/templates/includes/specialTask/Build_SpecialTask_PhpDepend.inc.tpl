{capture name="specialTaskLink"}<a href="#" class="quality">quality</a>{/capture}
{capture name="specialTaskPane"}
      <div id="quality" class="buildResultPane">
{if !isset($project_jdependChartFilename) && !isset($project_overviewPyramidFilename)}
No quality metrics were collected in this build. If you haven't enabled
this yet, please add a PHP_Depend task to this project's integration
builder, and configure it properly. If you already have this task enabled,
please check the raw output of this build for problems, such as a PHP Fatal error.
{else}
        <div id="jdependChart"><embed type="image/svg+xml" src="{UrlManager::getForAsset($project_overviewPyramidFilename, ['bid' => $project_build->getId()])}" width="392" height="270" /></div>
        <div id="overviewChart"><embed type="image/svg+xml" src="{UrlManager::getForAsset($project_jdependChartFilename, ['bid' => $project_build->getId()])}" width="392" height="270" /></div>
{/if}
      </div>
{/capture}