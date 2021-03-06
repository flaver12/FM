<?php
namespace Solaria\App\Controllers;

use Solaria\Framework\Controller\BaseController;
use Solaria\Framework\Acl\Acl;

use Solaria\App\Models\User;
use Solaria\App\Models\UserRole;
use Solaria\App\Models\Role;
use Solaria\App\Models\RolePermission;
use Solaria\App\Models\Post;
use Solaria\App\Models\Topic;
use Solaria\App\Models\Permission;
use Solaria\App\Models\Category;
use Solaria\App\Models\Resource;
use Solaria\App\Models\ResourceRole;
use Solaria\App\Models\Cronjobs;
use Solaria\App\Models\Page;
use Solaria\App\Forms\CategoryCreationForm;
use Solaria\App\Forms\CreateTopicForm;
use Solaria\App\Forms\CreateUserGroup;
use Solaria\App\Forms\BBCodeForm;
use Solaria\App\Forms\CreatePageForm;

use DirectoryIterator;

class AdminController extends BaseController {

    const VIEW_TOPIC = 'viewTopicAction';
    const DELETE_POST = 'deletePostAction';
    const EDIT_POST = 'editPostAction';
    const FORUM_BASE = 'ForumController';

    public function __construct() {
        parent::__construct();
        $this->set('user_count', count(User::findAll()));
        $this->set('post_count', count(Post::findAll()));
        $this->set('topic_count', count(Topic::findAll()));
    }

    public function indexAction() {
    }

    public function editForumAction() {
        //load category and build array
        $cats = Category::findAll();
        $catArr = array();
        foreach ($cats as $category) {
            $catArr[$category->getName()] = $category->getId();
        }
        //load groups and build array
        $roles = Role::findAll();
        $roleArr = array();
        foreach ($roles as $role) {
            $roleArr[$role->getName()] = $role->getId();
        }

        $this->set('topics', Topic::findAll());
        $this->set('catform', new CategoryCreationForm());
        $this->set('topicform', new CreateTopicForm($catArr, $roleArr));
    }

    public function userPermissionAction() {
        $allUsers = User::findAll();
        $userRoles = array();

        foreach ($allUsers as $user) {
            $roles = UserRole::findBy(array('user_id' => $user->getId()));
            $userRoles[$user->getUsername()] = array();
            foreach ($roles as $role) {
                $userRoles[$user->getUsername()][$role->getRole()->getId()] = $role->getRole()->getName();
            }
        }

        $this->set('allRoles', Role::findAll());
        $this->set('users', $allUsers);
        $this->set('userGroups', $userRoles);
        $this->set('user_group_form', new CreateUserGroup());
    }

    public function editUserAction($id) {
        if($this->request->isPost()) {

            if(count($this->request->getPost()) == 1) {
                //check if we have to delete some groups
                $roles = UserRole::findBy(array('user_id' => $this->request->getPost('user_id')));
                if(!empty($roles)) {
                    foreach ($roles as $role) {
                        UserRole::delete($role);
                    }
                }

                $this->response->redirect('admin/user-permission');
            } else {
                $post = $this->request->getPost();
                $userId = $post['user_id'];
                unset($post['user_id']);
                $roles = UserRole::findBy(array('user_id' => $this->request->getPost('user_id')));
                if(empty($roles)) {
                    foreach ($post as $key => $value) {
                        $userRole = new UserRole();
                        $userRole->setRole(Role::find($value));
                        $userRole->setUser(User::find($userId));
                        $userRole->save($userRole);
                    }
                } else {

                    //we do it simple at the moment, we remove all roles
                    //than we add the checked one, need to be redone soon!
                    foreach ($roles as $role) {
                        UserRole::delete($role);
                    }

                    foreach ($post as $key => $value) {
                        $userRole = new UserRole();
                        $userRole->setRole(Role::find($value));
                        $userRole->setUser(User::find($userId));
                        $userRole->save($userRole);
                    }
                }
                $this->response->redirect('admin/user-permission');
            }
        } else {
            $user = User::find($id);
            $this->set('editUser', $user);

            $this->set('userGroups',$this->getAcl()->getRole());
            $this->set('allGroups', Role::findAll());
        }
    }

    public function createCategoryAction() {
        if($this->request->isPost()) {
            $cat = new Category();
            $cat->setName($this->request->getPost('name'));
            $cat->save($cat);
            $this->response->redirect('admin');
        }
    }

    public function createTopicAction() {
        if($this->request->isPost()) {
            $topic = new Topic();
            $topic->setCategory(Category::find($this->request->getPost('category')));
            $topic->setName($this->request->getPost('name'));
            $topic->save($topic);
            $tempArr = $this->request->getPost();
            unset($tempArr['name']);
            unset($tempArr['category']);

            if(!empty($tempArr)) {
                $res = new Resource();
                $res->setName(self::FORUM_BASE.'.'.self::VIEW_TOPIC.'.'.$topic->getId());
                $res->save($res);
                foreach ($tempArr as $name => $id) {
                    $resRole = new ResourceRole();
                    $resRole->setResource($res);
                    $resRole->setRole(Role::find($id));
                    $resRole->save($resRole);
                }
            }

            $this->response->redirect('admin');
        }
        $this->response->redirect('admin');
    }

    public function createGroupAction() {
        if($this->request->isPost()) {
            $role = new Role();
            $role->setName($this->request->getPost('name'));
            $role->save($role);

            $this->response->redirect('admin/user-permission');

        } else {
            $this->response->redirect('admin');
        }
    }

    public function editTopicPermissionAction($id = null) {
        if($this->request->isPost()) {
            $topicId = $this->request->getPost('topic_id');
            $data = $this->request->getPost();
            unset($data['topic_id']);
            $resource = Resource::findBy(array('name' => self::FORUM_BASE.'.'.self::VIEW_TOPIC.'.'.$topicId));

            if(!empty($resource) && count($data) > 1) {
                $resourceRoles = $resource[0]->getResourceRole();
                $res = $resource[0];
                //we do it simple at the moment, we remove all roles
                //than we add the checked one, need to be redone soon!
                foreach ($resourceRoles as $role) {
                    ResourceRole::delete($role);
                }

                foreach ($data as $name => $id) {
                    $resRole = new ResourceRole();
                    $resRole->setResource($res);
                    $resRole->setRole(Role::find($id));
                    $resRole->save($resRole);
                }
            } else if(count($data) < 1 && !empty($resource)) {
                $resourceRoles = $resource[0]->getResourceRole();
                //we do it simple at the moment, we remove all roles
                //than we add the checked one, need to be redone soon!
                foreach ($resourceRoles as $role) {
                    ResourceRole::delete($role);
                }
                Resource::delete($resource[0]);

                $this->response->redirect('admin/edit-forum');
            } else {
                $res = new Resource();
                $res->setName(self::FORUM_BASE.'.'.self::VIEW_TOPIC.'.'.$topicId);
                $res->save($res);
                foreach ($data as $name => $id) {
                    $resRole = new ResourceRole();
                    $resRole->setResource($res);
                    $resRole->setRole(Role::find($id));
                    $resRole->save($resRole);
                }
            }



            $this->response->redirect('admin/edit-forum');
        } else {

            $topic = Topic::find($id);
            $roles = Role::findAll();
            $resArr = array();
            $resGroups = array();
            $resource = Resource::findBy(array('name' => self::FORUM_BASE.'.'.self::VIEW_TOPIC.'.'.$id));

            if(empty(!$resource)) {

                foreach ($resource as $res) {
                    $resRoles = $res->getResourceRole();
                    foreach ($resRoles as $resRole) {
                        $resGroups[$resRole->getRole()->getName()] = $resRole->getRole()->getId();
                    }
                }

            }

            foreach ($roles as $role) {
                array_push($resArr, $role);
            }

            $this->set('topic', $topic);
            $this->set('res', $resArr);
            $this->set('resGroup', $resGroups);
        }
    }

    public function newsFeedAction() {
        $this->set('form', new BBCodeForm('admin/create-news'));
    }

    public function deleteGroupAction($id) {
        $this->noRenderer();

        //find the group
        $role = Role::find($id);

        //delet first all relations with the users
        $userRoles = $role->getUserRole();
        if(!empty($userRoles)) {
            foreach ($userRoles as $userRole) {
                UserRole::delete($userRole);
            }
        }

        //delete now all role permissions
        $rolePermissions = $role->getRolePermission();
        if(!empty($rolePermissions)) {
            foreach ($rolePermissions as $rolePermission) {
                RolePermission::delete($rolePermission);
            }
        }

        //delete now all resources for that role
        $resourceRoles = $role->getResourceRole();
        if(!empty($resourceRoles)) {
            foreach ($resourceRoles as $resourceRole) {
                ResourceRole::delete($resourceRole);
            }
        }

        //finaly delete the group it self ;(
        Role::delete($role);

        $this->response->redirect('admin/user-permission');
    }

    public function editGroupAction($id) {
        if($this->request->isPost()) {
            $postArr = $this->request->getPost();
            $role = Role::find($id);
            $role->setName($postArr['role_name']);
            unset($postArr['role_name']);
            if(count($postArr) > 1) {

                $rolePermissions = $role->getRolePermission();
                //we do it simple at the moment, we remove all roles
                //than we add the checked one, need to be redone soon!
                foreach ($rolePermissions as $rolePermission) {
                    RolePermission::delete($rolePermission);
                }

                foreach ($postArr as $name => $value) {
                    $rolePermission = new RolePermission();
                    $rolePermission->setRole($role);
                    $rolePermission->setPermission(Permission::find($value));
                    $rolePermission->save($rolePermission);
                }

            }
            $this->response->redirect('admin/user-permission');
        } else {
            $role = Role::find($id);
            $rolePermissions = $role->getRolePermission();
            $allPermissions = Permission::findAll();
            $rolePermArray = array();

            foreach ($rolePermissions as $rolePermission) {
                $rolePermArray[$rolePermission->getPermission()->getName()] = $rolePermission->getPermission()->getId();
            }

            $this->set('role', $role);
            $this->set('all_permissions', $allPermissions);
            $this->set('role_permissions', $rolePermArray);
        }
    }

    public function cronjobAction() {

        $activeCrons = array();
        $inactiveCrons = array();
        $cronsInDB = Cronjobs::findAll();
        $dir = new DirectoryIterator(APP_PATH.'/Framework/Cronjob/Cronjobs');

        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                $cronName = str_replace(".php", "", $fileinfo->getFilename());
                $inactiveCrons[$cronName] = $cronName;
                foreach ($cronsInDB as $cron) {
                    if($cron->getName() == $cronName) {
                        array_push($activeCrons, $cron->getName());
                        unset($inactiveCrons[$cron->getName()]);
                    }
                }
            }
        }

        $this->set('inactive_crons', $inactiveCrons);
        $this->set('active_crons', $activeCrons);
    }

    public function pagesAction() {
        $this->set('pages', Page::findAll());
    }

    public function createPagesAction() {

        if($this->request->isPost()) {
            $page = new Page();
            $page->setTitle($this->request->getPost('title'));
            $page->setContent($this->request->getPost('content'));
            $page->setEnabled(1);
            $page->save($page);
            $this->flashSession->success('Page created');
            $this->response->redirect('admin/pages');
        } else {
            $this->set('pageform', new CreatePageForm());
        }

    }

}
