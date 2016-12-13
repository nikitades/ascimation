<?php

namespace App;

class Page extends CustomModel
{
    const ROOT_PAGE_NAME = '--корневая страница--';

    protected $fillable = [
        'id',
        'is_active',
        'is_in_menu',
        'name',
        'header',
        'meta_tags',
        'meta_description',
        'url',
        'parent_id',
        'view',
        'real_level',
        'is_root',
        'page_content',
        'pos'
    ];

    public function scopeAdminList($query)
    {
        return $query->where('is_root', '!=', 1)->orderBy('pos');
    }

    public function scopeMenu($query)
    {
        return $query->where('is_active', 1)->where('is_in_menu', 1)->where('real_level', '=', 1)->orderBy('pos');
    }

    public static function images()
    {
        return [
            'Картинка страницы' => 'PageImage',
//            'Изображение слайдера' => 'PageSliderImage',
        ];
    }

    public static function files()
    {
        return [
            'Файлы' => 'PageFile'
        ];
    }

    public function pageImages()
    {
        return $this->hasMany('App\PageImage', 'parent_id', 'id');
    }

    public function createUrl()
    {
        $parentsStack = [];
        $this->findParents($parentsStack);
        $url = '/pages';
        $prefix = '';
        foreach (array_reverse($parentsStack) as $item) {
            $url .= $prefix . $item->url;
            $prefix = $item->url == '/' ? '' : '/';
        }
        return $url;
    }

    public function getAncestry()
    {
        $parentsStack = [];
        $this->findParents($parentsStack);
        reset($parentsStack)->last = true;
        return array_reverse($parentsStack);
    }

    public function getPosterity()
    {
        $descendantsStack = [];
        $this->findDescendantsFlat($descendantsStack);
        return $descendantsStack;
    }

    public function findParents(&$array)
    {
        $parent = Page::find($this->parent_id);
        $array[$this->name] = $this;
        if ($parent && $parent->real_level >= 0) {
            $parent->findParents($array);
        }
    }

    public function findDescendantsFlat(&$array)
    {
        $descendants = Page::where('parent_id', $this->id)->get();
        if (sizeof($descendants)) {
            foreach ($descendants as $descendant) {
                $descendantDescendants = Page::where('parent_id', $descendant->id)->get();
                if (sizeof($descendantDescendants)) {
                    $descendant->findDescendantsFlat($array);
                }
                $array[] = $descendant;
            }
        }
    }


    /**
     * The service function to build the tree-like list of the pages.
     *
     * @param $root
     * @return mixed
     */
    public static function buildTree($root)
    {
        if (!$root) return false;
        $children = Page::where('parent_id', $root->id)->orderBy('pos')->get();
        if (sizeof($children)) {
            foreach ($children as $child) {
                Page::buildTree($child);
            }
        }

        $root->children = $children;
        return $root;
    }

    /**
     * Service function to flatten the tree-like site pages list.
     *
     * @param $page
     * @param $output
     * @return array
     */
    public static function flatten($page, $output)
    {
        if (!$page) return [Page::firstOrCreate(['is_root' => 1, 'real_level' => -1, 'name' => Page::ROOT_PAGE_NAME, 'url' => ''])];
        $page->name = str_repeat('&nbsp;&nbsp;', $page->real_level > 0 ? $page->real_level : 0) . ($page->real_level > 0 ? '┖' : '') . str_repeat('-', $page->real_level > 0 ? $page->real_level : 0) . ' ' . $page->name;
        $output[] = $page;
        if (sizeof($page->children)) {
            foreach ($page->children as $child) {
                $output = Page::flatten($child, $output);
            }
        }
        return $output;
    }

    /**
     * Returns the tree-like list of the pages. Checks if the true root page is created.
     *
     * @return array
     */
    public static function tree()
    {
        $tree = Page::buildTree(Page::where('real_level', 0)->first());
        return [$tree];
    }

    /**
     * Returns the flatten list of the site pages to show in the select boxes for example.
     *
     * @return mixed
     */
    public static function flatTree()
    {
        $tree = Page::buildTree(Page::where('real_level', 0)->first());
        return Page::flatten($tree, []);
    }

}
