<div class="error">
	<p><?php echo YD_NAME; ?> error: Your environment doesn't meet all of the system requirements listed below.</p>
	<p>If your PHP and WordPress versions are up to date, check that you have all the Plugin Requirements installed and activated.</p>
	<ul class="ul-disc">
		<li>
			<strong>PHP <?php echo YD_REQUIRED_PHP_VERSION; ?>+</strong>
			<em>(You're running version <?php echo PHP_VERSION; ?>)</em>
		</li>

		<li>
			<strong>WordPress <?php echo YD_REQUIRED_WP_VERSION; ?>+</strong>
			<em>(You're running version <?php echo esc_html( $wp_version ); ?>)</em>
		</li>

		<li>
			<strong>Plugin Requirements: WP REST API v1.2.2</strong>
		</li>

		<?php //<li><strong>Plugin XYZ</strong> activated</em></li> ?>
	</ul>

	<p>If you need to upgrade your version of PHP you can ask your hosting company for assistance, and if you need help upgrading WordPress you can refer to <a href="http://codex.wordpress.org/Upgrading_WordPress">the Codex</a>.</p>
</div>
