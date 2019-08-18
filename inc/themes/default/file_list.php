<header id="header">
	<div class="toolbar">
		<ul>
			<li><button type="button" data-operation="new-directory" data-path="<?php echo html($path);?>"><span class="fas fa-folder-plus"></span> <?php echo __('New directory');?></button></li>
			<li><button type="button" data-operation="upload" data-path="<?php echo html($path);?>"><span class="fas fa-upload"></span><?php echo __('Upload a file');?></button></li>
		</ul>
	</div>

	<nav class="breadcrumbs">
		<?php if (isset($breadcrumbs) && !empty($breadcrumbs)) : ?>
			<ul>
				<?php foreach ($breadcrumbs as $v) : ?>
					<li>
						<?php if (isset($v['href'])) : ?>
							<a href="<?php echo html($v['href']);?>">
						<?php endif;?>
						<?php if (isset($v['fa_icon'])) : ?>
							<?php echo $v['fa_icon'];?>
						<?php endif;?>
						<?php echo html($v['label']);?>
						<?php if (isset($v['href'])) : ?>
							</a>
						<?php endif;?>
					</li>
				<?php endforeach;?>
			</ul>
		<?php endif;?>
	</nav>
</header>
		
<div class="directory" data-path="<?php echo html($path);?>">
	<?php if ($num_files > 0) : ?>
		<?php for ($i = 0; $i < $num_files; $i++) : ?>
			<div class="directory-file" data-path="<?php echo html($files[$i]['path']);?>" data-public-path="<?php echo html($files[$i]['public_path']);?>" data-name="<?php echo html($files[$i]['name']);?>" data-is-dir="<?php if ($files[$i]['is_dir']) : ?>1<?php else : ?>0<?php endif;?>">
				<?php if ($files[$i]['is_dir']) : ?>
					<a href="?dir=<?php echo rawurlencode($files[$i]['path']);?>">
				<?php endif;?>
				<span class="directory-file-icon"><?php echo $files[$i]['fa_icon'];?></span>
				<span class="directory-file-name"><?php echo html($files[$i]['name']);?></span>
				<?php if ($files[$i]['is_dir']) : ?>
					</a>
				<?php endif;?>
			</div>
		<?php endfor;?>
	<?php else : ?>
		<p><?php echo __('Empty directory');?></p>
	<?php endif;?>
</div>