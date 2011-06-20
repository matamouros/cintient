{capture name="specialTaskLink"}<a href="#" class="junitReport">unit</a>{* | <a href="#" class="coverageReport">coverage</a>*}{/capture}
{capture name="specialTaskPane"}
      <div id="junitReport" class="buildResultPane">
{if !empty($project_buildJunit)}
{foreach from=$project_buildJunit item=classTest}
        <div class="classTest">{$classTest->getName()}</div>
        <div class="chart"><img width="{$smarty.const.CHART_JUNIT_DEFAULT_WIDTH}" src="{UrlManager::getForAsset($classTest->getChartFilename(), ['bid' => $project_build->getId()])}"></div>
{/foreach}
{else}
Due to a build error, the unit tests chart could not be generated. Please check the raw output of the build for problems, such as a PHP Fatal error.
{/if}
{*      </div>
      <div id="coverageReport" class="buildResultPane">
{if !empty($project_buildJunit)}
{foreach from=$project_buildJunit item=classTest}
        <div class="classTest">{$classTest->getName()}</div>
        <div class="chart"><img width="{$smarty.const.CHART_JUNIT_DEFAULT_WIDTH}" src="{UrlManager::getForAsset($classTest->getChartFilename(), ['bid' => $project_build->getId()])}"></div>
{/foreach}
{else}
Due to a build error, the unit tests chart could not be generated. Please check the raw output of the build for problems, such as a PHP Fatal error.
{/if}
      </div>*}
{/capture}