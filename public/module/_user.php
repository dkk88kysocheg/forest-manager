<?php 
/**
 * Вывод данных пользователя
 *
 * @package    Forest_Manager
 * @subpackage Forest_Manager/public
 */

$user_id = get_current_user_id();
$user = get_userdata( $user_id );
$name = $user->first_name . ' ' . $user->last_name;
$avatar_default = get_template_directory_uri() . '/assets/images/avatar_default.png'; 

?>  

<ul class="navbar-nav navbar-nav-right">
	<li class="nav-item dropdown d-none d-lg-inline-flex user-dropdown">
		<a class="nav-link dropdown-toggle" id="UserDropdown" href="#" data-toggle="dropdown" aria-expanded="false">
            <div class="profile-img img-sm rounded-circle" style="background-image: url(<?= $user->profile_img; ?>);"></div>
        </a>
		<div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
			<div class="dropdown-header text-center">
                <div class="profile-img img-md rounded-circle mx-auto" style="background-image: url(<?= $user->profile_img; ?>);"></div> 
				<p id="user-name" class="mb-1 mt-3" data-id-user="<?= $user_id; ?>" data-edit="<?= (string) $user->edit; ?>" data-view-list="<?= $user->view_list; ?>"><?= $name; ?></p>
				<p id="user-post" class="font-weight-light text-muted mb-0"><?= $user->post; ?></p>
			</div>
			<!--a class="dropdown-item" href="#"><i class="dropdown-item-icon icon-user text-primary"></i>Профиль</a-->
			<a class="dropdown-item" href="<?= wp_logout_url(); ?>"><i class="dropdown-item-icon icon-power text-primary"></i>Выход</a>
		</div>
	</li>
</ul> 