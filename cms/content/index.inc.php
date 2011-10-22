<?php
$header = 'This is your home page!';

ob_start();
?>

				<h3>Use this page as your main home!</h3>
				
				<div style="width:100%;height:200px;margin:10px 0;">
					<div class="border fleft" style="width:180px;height:180px;margin-top:10px;">
						<a href="/contact/"><img src="/images/map.png" alt="Map" style="width:180px;height:180px;" /></a>
					</div>
					<div class="fright" style="width:390px;height:200px;margin-top:10px;">
						<p>
							We are located in Santa Rosa's Historice Railroad
							Square District in downtown Santa Rosa.
							We are open daily:
						</p>
						<p>
							Monday thru Saturday, 10am &mdash; 6pm<br />
							Sunday, 11am &mdash; 5pm
						</p>
						<p>
							101 Third Street<br />
							Santa Rosa, CA 95401
						</p>
					</div>
				</div>
				<div class="clear"></div>
				
				<hr />

				<div>
					<h2>Latest News</h2>
					<?php
					// Sample of blog teaser include
					//include 'news/teaser.php';
					?>
					<div class="box">
						<p>Blog stuff can go here, or just static updates if needed</p>
					</div>
				</div>

<?php
$content = ob_get_contents();
ob_end_clean();
?>
