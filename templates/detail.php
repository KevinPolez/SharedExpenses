<?php
/**
 * @copyright Copyright (c) 2018 Kevin Polez <kevin@hypatie.xyz>
 *
 * @author Kevin Polez <kevin@hypatie.xyz>
 *
 * @license GNU AGPL version 3 or any later version
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

 use OCP\User;

script('sharedexpenses', 'script');
 style('sharedexpenses', 'style');

 \OCP\Util::addStyle('sharedexpenses', 'list');

 $userId = $_['userId'];
 $userMgr = $_['userMgr'];
 $urlGenerator = $_['urlGenerator'];
 $reckoning = $_['reckoning'];
 $lines = $_['lines'];

 if (
		$reckoning->getDescription() !== null &&
		$reckoning->getDescription() !== ''
	) {
		$description = str_replace(array('\r\n', '\r', '\n'), '<br/>', htmlspecialchars($reckoning->getDescription()));
	} else {
		$description = $l->t('No description provided.');
	}
 ?>

 <div id="app">
	<div id="app-content">
		<div id="controls" class="controls">
			<div id="breadcrump" class="breadcrump">
				<?php if (User::isLoggedIn()) : ?>
				<div class="crumb svg" data-dir="/">
					<a href="<?php p($urlGenerator->linkToRoute('sharedexpenses.page.index')); ?>">
						<img class="svg" src="<?php print_unescaped(\OCP\Template::image_path('core', 'places/home.svg')); ?>" alt="Home">
					</a>
				</div>
				<?php endif; ?>
				<div class="crumb svg last">
					<span><?php p($reckoning->getTitle()); ?></span>
				</div>

			</div>
    </div>

    <div class="lines" class="main-container">
      <div class="wordwrap description"><span><?php print_unescaped($description); ?></span></div>
      <div class="table main-container has-controls">

        <div class ="table-row table-header">
          <div class="wrapper group-master">
            <div class="flex-column qui">Qui</div>
            <div class="flex-column quoi">Quoi</div>
            <div class="flex-column quand">Quand</div>
            <div class="flex-column pour">Pour ...</div>
          </div>
        </div>

        <?php foreach ($lines as $line) : ?>
        <div class="table-row table-body">
          <div class="wrapper group-master">
            <div class="flex-column qui">XXX</div>
            <div class="flex-column quoi">XXX</div>
            <div class="flex-column quand">XXX</div>
            <div class="flex-column pour">XXX</div>
          </div>
        </div>
        <?php endforeach; ?>

      </div>
    </div>

  </div>
  <div id="app-sidebar" class="detailsView scroll-container">
    <div class="close flex-row">
			<a id="closeDetails" class="close icon-close has-tooltip-bottom" title="<?php p($l->t('Close details')); ?>" href="#" alt="<?php $l->t('Close'); ?>"></a>
    </div>

    <div class="header flex-row">
      <div class="reckoningInformation flex-column">
        <div class="authorRow user-cell flex-row">
          <div class="description leftLabel"><?php p($l->t('Owner')); ?></div>
          <div class="avatar has-tooltip-bottom" title="<?php p($reckoning->getOwner())?>"></div>
          <div class="author"><?php p($userMgr->get($reckoning->getOwner())->getDisplayName()); ?></div>
        </div>
      </div>

      <div class="cloud">
      </div>

      <div class="reckoningActions flex-column">
        <ul class="with-icons">
					<li>
						<a id="id_copy_<?php p($reckoning->getId()); ?>" class="icon-clippy has-tooltip-bottom svg copy-link" data-clipboard-text="<?php p($reckoningUrl); ?>" title="<?php p($l->t('Click to get link')); ?>" href="#">
							<?php p($l->t('Copy Link')); ?>
						</a>
					</li>

			    <?php if ($reckoning->getOwner() === $userId) : ?>
  					<li class="">
  						<a id="id_del_<?php p($reckoning->getId()); ?>" class="icon-delete svg delete-reckoning"  data-value="<?php p($reckoning->getTitle()); ?>" href="#">
  							<?php p($l->t('Delete Reckoning')); ?>
  						</a>
  					</li>
  					<li>
  						<a id="id_edit_<?php p($reckoning->getId()); ?>" class="icon-rename svg" href="<?php p($urlGenerator->linkToRoute('sharedexpenses.page.edit', ['hash' => $reckoning->getHash()])); ?>">
  							<?php p($l->t('Edit Reckoning')); ?>
  						</a>
  					</li>
			    <?php endif; ?>
        </ul>
      </div>

    </div>
  </div>
</div>
