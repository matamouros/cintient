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
  
*}{TemplateManager::providerFooter()}
  </div>
  <div id="footer" class="container">
    <div id="installationStats">Sentient since {$providerFooter_installDate|date_format}. Monitoring {$providerFooter_projectsCount} projects, with {$providerFooter_usersCount} user, built {$providerFooter_usersCount} times.</div>
    <div class="paragraph"><a href="http://code.google.com/p/cintient/">Cintient</a>, Continuous Integration made simple, is free software distributed under the New BSD License terms.</div>
    <div class="paragraph">Copyright &copy; 2011, <a href="mailto:pedro.matamouros@gmail.com">Pedro Mata-Mouros</a>. All rights reserved.</div>
  </div>
</body>
</html>