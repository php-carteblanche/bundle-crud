<?php
/**
 * This file is part of the CarteBlanche PHP framework.
 *
 * (c) Pierre Cassat <me@e-piwi.fr> and contributors
 *
 * License Apache-2.0 <http://github.com/php-carteblanche/carteblanche/blob/master/LICENSE>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
?>
<div class="content">

<?php if (!empty($return)): ?>
<p>To continue your normal navigation after reading this help, <a href="<?php 
	echo $return;
?>">clic here to go back to previous page</a>.</p>
<?php endif; ?>

<?php echo @$content; ?>

</div>