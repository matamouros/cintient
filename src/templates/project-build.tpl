{include file='includes/header.inc.tpl'}
{foreach from=$projectBuild_junit item=classTest}
  <h2>{$classTest->getName()}</h2>
  <img style="border:1px solid #999;" src="{URLManager::getForAsset($classTest->getChartFilename(), ['bid' => $projectBuild_build->getId()])}">
{/foreach}
{include file='includes/footer.inc.tpl'}