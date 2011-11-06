{capture name="specialTaskLink"}<li><a href="#quality">Quality</a></li>{/capture}
{capture name="specialTaskPane"}
      <div id="quality">
{if !isset($project_jdependChartFilename) && !isset($project_overviewPyramidFilename)}
No quality metrics were collected in this build. If you haven't enabled
this yet, please add a PHP_Depend task to this project's integration
builder, and configure it properly. If you already have this task enabled,
please check the raw output of this build for problems, such as a PHP Fatal error.
{else}
        <ul class="media-grid">
          <li><div id="jdependChart" class="chart"><embed type="image/svg+xml" src="{UrlManager::getForAsset($project_overviewPyramidFilename, ['pid' => $project_build->getProjectId(), 'bid' => $project_build->getId()])}" width="392" height="270" /></div></li>
          <li><div id="overviewChart" class="chart"><embed type="image/svg+xml" src="{UrlManager::getForAsset($project_jdependChartFilename, ['pid' => $project_build->getProjectId(), 'bid' => $project_build->getId()])}" width="392" height="270" /></div></li>
        </ul>
{/if}
      </div>
{/capture}