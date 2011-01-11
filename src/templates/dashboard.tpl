{*
  Cintient, Continuous Integration made simple.
  
  Copyright (c) 2011, Pedro Mata-Mouros <pedro.matamouros@gmail.com>
  All rights reserved.
  
  Redistribution and use in source and binary forms, with or without
  modification, are permitted provided that the following conditions
  are met:
  
  . Redistributions of source code must retain the above copyright
    notice, this list of conditions and the following disclaimer.
  
  . Redistributions in binary form must reproduce the above
    copyright notice, this list of conditions and the following
    disclaimer in the documentation and/or other materials provided
    with the distribution.
    
  . Neither the name of Pedro Mata-Mouros Fonseca, Cintient, nor
    the names of its contributors may be used to endorse or promote
    products derived from this software without specific prior
    written permission.
    
  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDER AND CONTRIBUTORS
  "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
  LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
  FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
  COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
  INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
  BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
  CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
  LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
  ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
  POSSIBILITY OF SUCH DAMAGE.
  
*}{include file='includes/header.inc.tpl' menuLeft="Dashboard" menuRight="Create a <a href=\"{URLManager::getForProjectNew()}\">new project</a>."}
{if !empty($dashboard_projectList)}
    <div id="projectListContainer" class="container">
      <ul>
{foreach $dashboard_projectList as $project}
      <li class="projectDraggableContainer container">
        <a href="{URLManager::getForProjectView($project)}" class="projectLink">
        <div class="projectAvatar40x40"><img src="/imgs/redhalo_90x90.jpg" width="40" height="40"></div>
        <div class="projectStatus projectStatus{if $project->getStatus()==Project::STATUS_OK}Ok{else}Failed{/if}"></div>
        <div class="projectDetails">
          <div class="projectTitle">{$project->getTitle()}</div>
          <div class="projectStats">Latest build on Jan 9, 2011</div>
          <div class="projectStats">Current version: 1.0.9</div>
          {*<div class="projectStats">Production version: 1.0.9</div>*}
        </div>
        </a>
      </li>
{/foreach}
      </ul>
    </div>
<script type="text/javascript">
// <![CDATA[
$(document).ready(function() {
  $('.projectDraggableContainer').each( function() {
  	$(this).click(function() {
  		window.location = $(this).find('a').attr('href');
    });
  	$(this).hover(
  		function() {
        $(this).css({
      	  "cursor" : "pointer",
      	  "border" : "2px solid rgb(255,40,0)",
      	  "box-shadow" : "0px 0px 40px rgb(255,40,0)",
          "-webkit-box-shadow" : "rgb(255,40,0) 0px 0px 40px",
          "-moz-box-shadow" : "rgb(255,40,0) 0px 0px 30px"
        });
      },
      function() {
      	$(this).css({
      	  "cursor" : "default",
      	  "border" : "2px solid #999",
      	  "box-shadow" : "2px 2px 10px #111",
      	  "-webkit-box-shadow" : "#111 2px 2px 10px",
      	  "-moz-box-shadow" : "#111 2px 2px 10px"
        });
      });
  });
});
// ]]> 
</script>
{else}
    <div class="messageInfo container">You don't have any projects, but you can always <a href="{URLManager::getForProjectNew()}">create a new one</a>.</div>
{/if}
{include file='includes/footer.inc.tpl'}