<?php 
/*
 * Author: Snigdha Sivadas
 * Description: Admin.php
 */

?> 
<script type="text/javascript" src="src/admin/admin.js"></script>
<div> <?php  print_r($_SESSION); $coreid= $_SESSION['username'];  ?> </div>
<div class="easyui-layout" style="width:auto;height:800px;" >
                        <div region="west" split="true" title="Navigator" style="width:250px;">
                                <p style="padding:5px;margin:0;">Admin Options:</p>
                                <ul>
                                        <li><a href="javascript:void(0)" onclick="open1('src/admin/createmanual.php')">Create User Manual</a></li>
                                        <li><a href="javascript:void(0)" onclick="open1('src/admin/frameworkc.php')">Framework</a></li>
                                        <li><a href="javascript:void(0)" onclick="showcontent('Not Impletemented')">Packages</a></li>
                                        <li><a href="javascript:void(0)" onclick="open1('src/admin/testlibraryc.php?coreid=<?php echo $coreid?>')">TestLibrary</a></li>
                                </ul>
                        </div>
                        <div id="content" region="center" title="Language" style="padding:5px;">
                                <iframe id="cc" frameborder="0" scrolling="auto" style="width:100%;height:100%"></iframe>
                        </div>
</div>
