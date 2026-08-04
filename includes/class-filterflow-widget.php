<?php
namespace FilterFlow_Posts;

use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Widget extends Widget_Base {
	public function get_name(): string {
		return 'filterflow_posts';
	}

	public function get_title(): string {
		return __( 'FilterFlow Posts', 'filterflow-posts' );
	}

	public function get_icon(): string {
		return 'eicon-posts-grid';
	}

	public function get_categories(): array {
		return array( 'filterflow' );
	}

	public function get_keywords(): array {
		return array( 'posts', 'filter', 'ajax', 'grid', 'blog', 'category', 'author', 'meta', 'archive' );
	}

	public function get_style_depends(): array {
		return array( 'filterflow-posts' );
	}

	public function get_script_depends(): array {
		return array( 'filterflow-posts' );
	}

	protected function register_controls(): void {
		$this->register_content_controls();
		$this->register_layout_controls();
		$this->register_heading_area_style_controls();
		$this->register_filter_bar_style_controls();
		$this->register_filter_style_controls();
		$this->register_card_style_controls();
		$this->register_badge_style_controls();
		$this->register_content_style_controls();
		$this->register_pagination_style_controls();
	}

	private function register_content_controls(): void {
		$this->start_controls_section(
			'section_intro',
			array( 'label' => __( 'Heading', 'filterflow-posts' ) )
		);

		$this->add_control(
			'show_intro',
			array(
				'label'        => __( 'Show Heading', 'filterflow-posts' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'filterflow-posts' ),
				'label_off'    => __( 'Hide', 'filterflow-posts' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'heading',
			array(
				'label'       => __( 'Title', 'filterflow-posts' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'All Posts', 'filterflow-posts' ),
				'label_block' => true,
				'condition'   => array( 'show_intro' => 'yes' ),
			)
		);

		$this->add_control(
			'description',
			array(
				'label'       => __( 'Description', 'filterflow-posts' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => __( 'Insights, tutorials, and ideas from the blog.', 'filterflow-posts' ),
				'rows'        => 3,
				'condition'   => array( 'show_intro' => 'yes' ),
			)
		);

		$this->add_control(
			'heading_tag',
			array(
				'label'     => __( 'Title HTML Tag', 'filterflow-posts' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'h2',
				'options'   => array(
					'h1' => 'H1',
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'div' => 'DIV',
				),
				'condition' => array( 'show_intro' => 'yes' ),
			)
		);

		$this->add_responsive_control(
			'heading_alignment',
			array(
				'label'     => __( 'Alignment', 'filterflow-posts' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array( 'title' => __( 'Left', 'filterflow-posts' ), 'icon' => 'eicon-text-align-left' ),
					'center' => array( 'title' => __( 'Center', 'filterflow-posts' ), 'icon' => 'eicon-text-align-center' ),
					'right'  => array( 'title' => __( 'Right', 'filterflow-posts' ), 'icon' => 'eicon-text-align-right' ),
				),
				'default'   => 'left',
				'toggle'    => false,
				'selectors' => array( '{{WRAPPER}} .ffp-intro' => 'text-align: {{VALUE}};' ),
				'condition' => array( 'show_intro' => 'yes' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_query',
			array( 'label' => __( 'Query', 'filterflow-posts' ) )
		);

		$this->add_control(
			'categories',
			array(
				'label'       => __( 'Categories', 'filterflow-posts' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $this->get_category_options(),
				'description' => __( 'Leave empty to use all non-empty categories.', 'filterflow-posts' ),
			)
		);

		$this->add_control(
			'posts_per_page',
			array(
				'label'   => __( 'Posts Per Page', 'filterflow-posts' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 6,
				'min'     => 1,
				'max'     => 24,
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'   => __( 'Order By', 'filterflow-posts' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'date',
				'options' => array(
					'date'          => __( 'Date', 'filterflow-posts' ),
					'modified'      => __( 'Modified Date', 'filterflow-posts' ),
					'title'         => __( 'Title', 'filterflow-posts' ),
					'menu_order'    => __( 'Menu Order', 'filterflow-posts' ),
					'comment_count' => __( 'Comment Count', 'filterflow-posts' ),
					'rand'          => __( 'Random', 'filterflow-posts' ),
				),
			)
		);

		$this->add_control(
			'order',
			array(
				'label'   => __( 'Order', 'filterflow-posts' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'DESC',
				'options' => array(
					'DESC' => __( 'Descending', 'filterflow-posts' ),
					'ASC'  => __( 'Ascending', 'filterflow-posts' ),
				),
			)
		);

		$this->add_control(
			'ignore_sticky',
			array(
				'label'        => __( 'Ignore Sticky Posts', 'filterflow-posts' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'exclude_current',
			array(
				'label'        => __( 'Exclude Current Post', 'filterflow-posts' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_filters',
			array( 'label' => __( 'Filters', 'filterflow-posts' ) )
		);

		$this->add_control(
			'show_filters',
			array(
				'label'        => __( 'Show Category Filters', 'filterflow-posts' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_responsive_control(
			'filter_alignment',
			array(
				'label'     => __( 'Filter Alignment', 'filterflow-posts' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'start'  => array( 'title' => __( 'Left', 'filterflow-posts' ), 'icon' => 'eicon-h-align-left' ),
					'center' => array( 'title' => __( 'Center', 'filterflow-posts' ), 'icon' => 'eicon-h-align-center' ),
					'end'    => array( 'title' => __( 'Right', 'filterflow-posts' ), 'icon' => 'eicon-h-align-right' ),
				),
				'default'   => 'start',
				'toggle'    => false,
				'selectors' => array(
					'{{WRAPPER}} .ffp-filter-bar__inner' => 'justify-content: {{VALUE}};',
					'{{WRAPPER}} .ffp-filter-chips'     => 'justify-content: {{VALUE}};',
				),
				'condition' => array( 'show_filters' => 'yes' ),
			)
		);

		$this->add_control(
			'filter_bar_width',
			array(
				'label'        => __( 'Filter Bar Width', 'filterflow-posts' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'full',
				'options'      => array(
					'contained' => __( 'Contained', 'filterflow-posts' ),
					'full'      => __( 'Full Browser Width', 'filterflow-posts' ),
				),
				'prefix_class' => 'ffp-filter-width-',
				'condition'    => array( 'show_filters' => 'yes' ),
			)
		);

		$this->add_control(
			'sticky_filters',
			array(
				'label'        => __( 'Sticky Filter Bar', 'filterflow-posts' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'prefix_class' => 'ffp-filter-sticky-',
				'condition'    => array( 'show_filters' => 'yes' ),
			)
		);

		$this->add_responsive_control(
			'sticky_filter_offset',
			array(
				'label'      => __( 'Sticky Top Offset', 'filterflow-posts' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 300 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 0 ),
				'selectors'  => array( '{{WRAPPER}} .ffp-filter-bar' => '--ffp-sticky-user-offset: {{SIZE}}{{UNIT}};' ),
				'condition'  => array( 'show_filters' => 'yes', 'sticky_filters' => 'yes' ),
			)
		);

		$this->add_control(
			'all_label',
			array(
				'label'     => __( 'All Label', 'filterflow-posts' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'All', 'filterflow-posts' ),
				'condition' => array( 'show_filters' => 'yes' ),
			)
		);

		$this->add_control(
			'more_label',
			array(
				'label'     => __( 'More Label', 'filterflow-posts' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'More', 'filterflow-posts' ),
				'condition' => array( 'show_filters' => 'yes' ),
			)
		);

		$this->add_control(
			'mobile_filter_label',
			array(
				'label'     => __( 'Responsive Filter Button', 'filterflow-posts' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Filter', 'filterflow-posts' ),
				'condition' => array( 'show_filters' => 'yes' ),
			)
		);


		$this->add_control(
			'responsive_filters_heading',
			array(
				'label'     => __( 'Responsive Behaviour', 'filterflow-posts' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array( 'show_filters' => 'yes' ),
			)
		);

		$this->add_control(
			'tablet_filter_layout',
			array(
				'label'       => __( 'Tablet Filter Layout', 'filterflow-posts' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'select',
				'options'     => array(
					'select'   => __( 'Single Category Dropdown (Recommended)', 'filterflow-posts' ),
					'auto-fit' => __( 'Auto-fit Chips + More', 'filterflow-posts' ),
				),
				'description' => __( 'The recommended tablet layout uses one category control, avoiding duplicate Filter and dropdown buttons.', 'filterflow-posts' ),
				'render_type' => 'template',
				'condition'   => array( 'show_filters' => 'yes' ),
			)
		);

		$this->add_control(
			'mobile_filter_layout',
			array(
				'label'        => __( 'Mobile Filter Layout', 'filterflow-posts' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'fixed-all',
				'options'      => array(
					'fixed-all'       => __( 'Filter + All + Quick Categories (Recommended)', 'filterflow-posts' ),
					'filter-all-only' => __( 'Filter + All Only', 'filterflow-posts' ),
					'quick-scroll'    => __( 'Filter + All + Swipeable Categories', 'filterflow-posts' ),
					'swipe-only'     => __( 'Swipeable Categories Only', 'filterflow-posts' ),
				),
				'prefix_class' => 'ffp-mobile-layout-',
				'render_type'  => 'template',
				'description'  => __( 'The selected category is always moved to the first position. Swipeable Categories Only removes the Filter button and keeps All inside the swipe row.', 'filterflow-posts' ),
				'condition'    => array( 'show_filters' => 'yes' ),
			)
		);

		$this->add_control(
			'filter_tablet_breakpoint',
			array(
				'label'       => __( 'Tablet Compact Breakpoint', 'filterflow-posts' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 1024,
				'min'         => 700,
				'max'         => 1600,
				'step'        => 1,
				'description' => __( 'Uses the widget width, not only the browser width, so it also works inside narrow Elementor containers.', 'filterflow-posts' ),
				'condition'   => array( 'show_filters' => 'yes' ),
			)
		);

		$this->add_control(
			'filter_mobile_breakpoint',
			array(
				'label'     => __( 'Mobile Stack Breakpoint', 'filterflow-posts' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 767,
				'min'       => 320,
				'max'       => 1024,
				'step'      => 1,
				'condition' => array( 'show_filters' => 'yes' ),
			)
		);

		$this->add_control(
			'prevent_header_overlap',
			array(
				'label'        => __( 'Prevent Header Overlap', 'filterflow-posts' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'description'  => __( 'Automatically adds clearance when a theme or Elementor header overlaps the filter bar at a breakpoint.', 'filterflow-posts' ),
				'condition'    => array( 'show_filters' => 'yes' ),
			)
		);

		$this->add_control(
			'header_collision_gap',
			array(
				'label'     => __( 'Header Safety Gap', 'filterflow-posts' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 16,
				'min'       => 0,
				'max'       => 120,
				'step'      => 1,
				'condition' => array( 'show_filters' => 'yes', 'prevent_header_overlap' => 'yes' ),
			)
		);


		$this->add_responsive_control(
			'manual_header_clearance',
			array(
				'label'       => __( 'Manual Header Clearance', 'filterflow-posts' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px' ),
				'range'       => array( 'px' => array( 'min' => 0, 'max' => 360 ) ),
				'default'     => array( 'unit' => 'px', 'size' => 0 ),
				'description' => __( 'Optional fallback for unusual custom headers that cannot be detected automatically.', 'filterflow-posts' ),
				'selectors'   => array( '{{WRAPPER}} .ffp-widget' => '--ffp-manual-header-clearance: {{SIZE}}{{UNIT}};' ),
				'condition'   => array( 'show_filters' => 'yes' ),
			)
		);

		$this->add_control(
			'mobile_active_prefix',
			array(
				'label'       => __( 'Selected Category Prefix', 'filterflow-posts' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => __( 'Topic:', 'filterflow-posts' ),
				'condition'   => array( 'show_filters' => 'yes' ),
				'description' => __( 'Shown before the selected category in the tablet dropdown.', 'filterflow-posts' ),
			)
		);

		$this->add_control(
			'mobile_sheet_title',
			array(
				'label'     => __( 'Filter Panel Title', 'filterflow-posts' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Filter Topics', 'filterflow-posts' ),
				'condition' => array( 'show_filters' => 'yes' ),
			)
		);

		$this->add_control(
			'mobile_apply_label',
			array(
				'label'     => __( 'Apply Button Label', 'filterflow-posts' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Apply Filter', 'filterflow-posts' ),
				'condition' => array( 'show_filters' => 'yes' ),
			)
		);

		$this->add_control(
			'mobile_clear_label',
			array(
				'label'     => __( 'Clear Button Label', 'filterflow-posts' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Clear Filter', 'filterflow-posts' ),
				'condition' => array( 'show_filters' => 'yes' ),
			)
		);

		$this->add_control(
			'show_filter_counts',
			array(
				'label'        => __( 'Show Category Counts', 'filterflow-posts' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'condition'    => array( 'show_filters' => 'yes' ),
			)
		);


		$this->add_control(
			'show_filter_icons',
			array(
				'label'        => __( 'Show Filter Icons', 'filterflow-posts' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'description'  => __( 'Enabled by default. FilterFlow automatically chooses a suitable icon from each category name, and you can override individual categories below.', 'filterflow-posts' ),
				'condition'    => array( 'show_filters' => 'yes' ),
			)
		);

		$this->add_control(
			'all_filter_icon',
			array(
				'label'       => __( 'All Filter Icon', 'filterflow-posts' ),
				'type'        => Controls_Manager::ICONS,
				'description' => __( 'A grid icon is used automatically. Select an icon here only when you want to override it.', 'filterflow-posts' ),
				'condition'   => array( 'show_filters' => 'yes', 'show_filter_icons' => 'yes' ),
			)
		);

		$filter_icon_repeater = new Repeater();
		$filter_icon_repeater->add_control( 'category_id', array( 'label' => __( 'Category', 'filterflow-posts' ), 'type' => Controls_Manager::SELECT, 'options' => $this->get_category_options() ) );
		$filter_icon_repeater->add_control( 'icon', array( 'label' => __( 'Icon', 'filterflow-posts' ), 'type' => Controls_Manager::ICONS ) );
		$this->add_control(
			'category_filter_icons',
			array(
				'label'       => __( 'Category Icon Overrides', 'filterflow-posts' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $filter_icon_repeater->get_controls(),
				'title_field' => '{{{ category_id }}}',
				'description' => __( 'Optional. Categories without an override use an automatic icon based on the category name.', 'filterflow-posts' ),
				'condition'   => array( 'show_filters' => 'yes', 'show_filter_icons' => 'yes' ),
			)
		);

		$this->add_control(
			'filter_orderby',
			array(
				'label'     => __( 'Category Order By', 'filterflow-posts' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'name',
				'options'   => array(
					'name'  => __( 'Name', 'filterflow-posts' ),
					'count' => __( 'Post Count', 'filterflow-posts' ),
					'id'    => __( 'Term ID', 'filterflow-posts' ),
				),
				'condition' => array( 'show_filters' => 'yes' ),
			)
		);

		$this->add_control(
			'filter_order',
			array(
				'label'     => __( 'Category Order', 'filterflow-posts' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'ASC',
				'options'   => array( 'ASC' => __( 'Ascending', 'filterflow-posts' ), 'DESC' => __( 'Descending', 'filterflow-posts' ) ),
				'condition' => array( 'show_filters' => 'yes' ),
			)
		);

		$this->add_control(
			'tablet_visible_filters',
			array(
				'label'       => __( 'Visible Filters on Tablet', 'filterflow-posts' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 6,
				'min'         => 1,
				'max'         => 20,
				'description' => __( 'Maximum preferred chip count in Tablet Auto-fit mode. Width measurement can reduce it further.', 'filterflow-posts' ),
				'condition'   => array( 'show_filters' => 'yes', 'tablet_filter_layout' => 'auto-fit' ),
			)
		);

		$this->add_control(
			'mobile_visible_filters',
			array(
				'label'       => __( 'Quick Categories on Mobile', 'filterflow-posts' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 3,
				'min'         => 0,
				'max'         => 8,
				'description' => __( 'The active category is always placed first. Filter provides access to every category.', 'filterflow-posts' ),
				'condition'   => array( 'show_filters' => 'yes' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_card_content',
			array( 'label' => __( 'Post Card', 'filterflow-posts' ) )
		);

		$this->add_control( 'show_image', $this->switcher_control( __( 'Featured Image', 'filterflow-posts' ), 'yes' ) );

		$this->add_control(
			'image_size',
			array(
				'label'     => __( 'Image Size', 'filterflow-posts' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'large',
				'options'   => array(
					'thumbnail'    => __( 'Thumbnail', 'filterflow-posts' ),
					'medium'       => __( 'Medium', 'filterflow-posts' ),
					'medium_large' => __( 'Medium Large', 'filterflow-posts' ),
					'large'        => __( 'Large', 'filterflow-posts' ),
					'full'         => __( 'Full', 'filterflow-posts' ),
				),
				'condition' => array( 'show_image' => 'yes' ),
			)
		);

		$this->add_control(
			'card_title_tag',
			array(
				'label'   => __( 'Post Title HTML Tag', 'filterflow-posts' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h3',
				'options' => array( 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'h5' => 'H5', 'h6' => 'H6', 'div' => 'DIV' ),
			)
		);

		$this->add_control( 'show_excerpt', $this->switcher_control( __( 'Excerpt', 'filterflow-posts' ), 'yes' ) );

		$this->add_control(
			'excerpt_source',
			array(
				'label'       => __( 'Excerpt Source', 'filterflow-posts' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'smart',
				'options'     => array(
					'smart'     => __( 'Smart Elementor Content', 'filterflow-posts' ),
					'manual'    => __( 'Manual WordPress Excerpt Only', 'filterflow-posts' ),
					'wordpress' => __( 'WordPress Generated Excerpt', 'filterflow-posts' ),
				),
				'description' => __( 'Smart mode ignores Elementor Heading widgets and uses the first Text Editor content. A manual excerpt always takes priority.', 'filterflow-posts' ),
				'condition'   => array( 'show_excerpt' => 'yes' ),
			)
		);

		$this->add_control(
			'excerpt_length',
			array(
				'label'     => __( 'Excerpt Length', 'filterflow-posts' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 20,
				'min'       => 5,
				'max'       => 80,
				'condition' => array( 'show_excerpt' => 'yes' ),
			)
		);

		$this->add_control( 'show_author', $this->switcher_control( __( 'Author', 'filterflow-posts' ), 'yes' ) );

		$this->add_control(
			'author_prefix',
			array(
				'label'       => __( 'Author Prefix', 'filterflow-posts' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'By', 'filterflow-posts' ),
				'placeholder' => __( 'By', 'filterflow-posts' ),
				'condition'   => array( 'show_author' => 'yes' ),
			)
		);

		$this->add_control(
			'show_author_avatar',
			array(
				'label'        => __( 'Author Avatar', 'filterflow-posts' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array( 'show_author' => 'yes' ),
			)
		);

		$this->add_control(
			'author_avatar_size',
			array(
				'label'     => __( 'Avatar Size', 'filterflow-posts' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 22,
				'min'       => 16,
				'max'       => 64,
				'condition' => array( 'show_author' => 'yes', 'show_author_avatar' => 'yes' ),
			)
		);

		$this->add_control(
			'link_author',
			array(
				'label'        => __( 'Link Author Archive', 'filterflow-posts' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array( 'show_author' => 'yes' ),
			)
		);

		$this->add_control( 'show_date', $this->switcher_control( __( 'Date', 'filterflow-posts' ), 'yes' ) );

		$this->add_control(
			'date_source',
			array(
				'label'     => __( 'Date Source', 'filterflow-posts' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'published',
				'options'   => array( 'published' => __( 'Published Date', 'filterflow-posts' ), 'modified' => __( 'Last Modified Date', 'filterflow-posts' ) ),
				'condition' => array( 'show_date' => 'yes' ),
			)
		);

		$this->add_control(
			'date_format',
			array(
				'label'       => __( 'Custom Date Format', 'filterflow-posts' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => __( 'Leave blank for WordPress format', 'filterflow-posts' ),
				'description' => __( 'Uses PHP date format characters, for example M j, Y.', 'filterflow-posts' ),
				'condition'   => array( 'show_date' => 'yes' ),
			)
		);

		$this->add_control( 'show_reading_time', $this->switcher_control( __( 'Reading Time', 'filterflow-posts' ), 'yes' ) );

		$this->add_control(
			'words_per_minute',
			array(
				'label'     => __( 'Reading Speed (WPM)', 'filterflow-posts' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 220,
				'min'       => 100,
				'max'       => 500,
				'condition' => array( 'show_reading_time' => 'yes' ),
			)
		);

		$this->add_control( 'show_comments', $this->switcher_control( __( 'Comment Count', 'filterflow-posts' ), '' ) );
		$this->add_control( 'show_arrow', $this->switcher_control( __( 'Arrow Link', 'filterflow-posts' ), 'yes' ) );
		$this->add_control( 'open_new_tab', $this->switcher_control( __( 'Open Posts in New Tab', 'filterflow-posts' ), '' ) );

		$this->end_controls_section();

		$this->start_controls_section(
			'section_category_badges_content',
			array( 'label' => __( 'Category Badges', 'filterflow-posts' ) )
		);

		$this->add_control(
			'show_badge',
			array(
				'label'        => __( 'Show Category Badges', 'filterflow-posts' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'filterflow-posts' ),
				'label_off'    => __( 'Hide', 'filterflow-posts' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'render_type'  => 'template',
			)
		);

		$this->add_control(
			'badge_position',
			array(
				'label'       => __( 'Placement', 'filterflow-posts' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'content',
				'options'     => array(
					'above-image'        => __( 'Above Featured Image', 'filterflow-posts' ),
					'content'            => __( 'Below Image / Above Post Title', 'filterflow-posts' ),
					'image-top-left'     => __( 'Overlay — Top Left', 'filterflow-posts' ),
					'image-top-center'   => __( 'Overlay — Top Center', 'filterflow-posts' ),
					'image-top-right'    => __( 'Overlay — Top Right', 'filterflow-posts' ),
					'image-middle-left'  => __( 'Overlay — Center Left', 'filterflow-posts' ),
					'image-center'       => __( 'Overlay — Center', 'filterflow-posts' ),
					'image-middle-right' => __( 'Overlay — Center Right', 'filterflow-posts' ),
					'image-bottom-left'  => __( 'Overlay — Bottom Left', 'filterflow-posts' ),
					'image-bottom-center'=> __( 'Overlay — Bottom Center', 'filterflow-posts' ),
					'image-bottom-right' => __( 'Overlay — Bottom Right', 'filterflow-posts' ),
				),
				'description' => __( 'Overlay positions are used when a featured image exists. Posts without an image automatically place badges above the post title.', 'filterflow-posts' ),
				'condition'   => array( 'show_badge' => 'yes' ),
				'render_type' => 'template',
			)
		);

		$this->add_control(
			'link_badges',
			array(
				'label'        => __( 'Link Badges to Categories', 'filterflow-posts' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'condition'    => array( 'show_badge' => 'yes' ),
				'render_type'  => 'template',
			)
		);

		$this->add_control(
			'badge_limit',
			array(
				'label'       => __( 'Maximum Category Badges', 'filterflow-posts' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 0,
				'min'         => 0,
				'max'         => 10,
				'description' => __( 'Use 0 to show every category assigned to the post.', 'filterflow-posts' ),
				'condition'   => array( 'show_badge' => 'yes' ),
				'render_type' => 'template',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_pagination',
			array( 'label' => __( 'Pagination', 'filterflow-posts' ) )
		);

		$this->add_control(
			'pagination_type',
			array(
				'label'   => __( 'Type', 'filterflow-posts' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'numbers',
				'options' => array(
					'numbers'   => __( 'Page Numbers', 'filterflow-posts' ),
					'load_more' => __( 'Load More Button', 'filterflow-posts' ),
					'none'      => __( 'None', 'filterflow-posts' ),
				),
			)
		);

		$this->add_control(
			'load_more_label',
			array(
				'label'     => __( 'Button Label', 'filterflow-posts' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Load more', 'filterflow-posts' ),
				'condition' => array( 'pagination_type' => 'load_more' ),
			)
		);

		$this->add_control(
			'no_posts_message',
			array(
				'label'   => __( 'No Posts Message', 'filterflow-posts' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'No posts found.', 'filterflow-posts' ),
			)
		);

		$this->end_controls_section();
	}

	private function register_layout_controls(): void {
		$this->start_controls_section(
			'section_layout',
			array(
				'label' => __( 'Layout', 'filterflow-posts' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'          => __( 'Columns', 'filterflow-posts' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => array( '1' => '1', '2' => '2', '3' => '3', '4' => '4' ),
				'selectors'      => array( '{{WRAPPER}} .ffp-grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));' ),
			)
		);

		$this->add_responsive_control(
			'card_content_alignment',
			array(
				'label'     => __( 'Card Content Alignment', 'filterflow-posts' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'start'  => array( 'title' => __( 'Left', 'filterflow-posts' ), 'icon' => 'eicon-text-align-left' ),
					'center' => array( 'title' => __( 'Center', 'filterflow-posts' ), 'icon' => 'eicon-text-align-center' ),
					'end'    => array( 'title' => __( 'Right', 'filterflow-posts' ), 'icon' => 'eicon-text-align-right' ),
				),
				'default'   => 'start',
				'toggle'    => false,
				'selectors' => array(
					'{{WRAPPER}} .ffp-card__body'   => 'align-items: {{VALUE}}; text-align: {{VALUE}};',
					'{{WRAPPER}} .ffp-card__badges' => 'justify-content: {{VALUE}};',
					'{{WRAPPER}} .ffp-card__footer' => 'text-align: start;',
				),
			)
		);

		$this->add_responsive_control(
			'grid_gap',
			array(
				'label'      => __( 'Card Gap', 'filterflow-posts' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 26 ),
				'selectors'  => array( '{{WRAPPER}} .ffp-grid' => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'intro_spacing',
			array(
				'label'      => __( 'Heading Bottom Spacing', 'filterflow-posts' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 20 ),
				'selectors'  => array( '{{WRAPPER}} .ffp-intro' => 'margin-bottom: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'filter_spacing',
			array(
				'label'      => __( 'Filter Bottom Spacing', 'filterflow-posts' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 28 ),
				'selectors'  => array( '{{WRAPPER}} .ffp-filter-bar' => 'margin-bottom: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();
	}

	private function register_heading_area_style_controls(): void {
		$this->start_controls_section(
			'section_heading_area_style',
			array( 'label' => __( 'Heading Area', 'filterflow-posts' ), 'tab' => Controls_Manager::TAB_STYLE )
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array( 'name' => 'intro_background', 'types' => array( 'classic', 'gradient' ), 'selector' => '{{WRAPPER}} .ffp-intro' )
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array( 'name' => 'intro_border', 'selector' => '{{WRAPPER}} .ffp-intro' )
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array( 'name' => 'intro_shadow', 'selector' => '{{WRAPPER}} .ffp-intro' )
		);

		$this->add_responsive_control(
			'intro_padding',
			array(
				'label'      => __( 'Padding', 'filterflow-posts' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem', '%' ),
				'selectors'  => array( '{{WRAPPER}} .ffp-intro' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'intro_radius',
			array(
				'label'      => __( 'Border Radius', 'filterflow-posts' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array( '{{WRAPPER}} .ffp-intro' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'intro_description_spacing',
			array(
				'label'      => __( 'Title to Description Gap', 'filterflow-posts' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 10 ),
				'selectors'  => array( '{{WRAPPER}} .ffp-intro__description' => 'margin-top: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();
	}

	private function register_filter_bar_style_controls(): void {
		$this->start_controls_section(
			'section_filter_bar_style',
			array( 'label' => __( 'Filter Bar', 'filterflow-posts' ), 'tab' => Controls_Manager::TAB_STYLE )
		);

		$this->add_control(
			'filter_bar_surface',
			array(
				'label'        => __( 'Surface Background & Shadow', 'filterflow-posts' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'prefix_class' => 'ffp-filter-surface-',
				'description'  => __( 'Adds a clean white background and light shadow. The controls below can override the preset.', 'filterflow-posts' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array( 'name' => 'filter_bar_background', 'types' => array( 'classic', 'gradient' ), 'selector' => '{{WRAPPER}} .ffp-filter-bar' )
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array( 'name' => 'filter_bar_border', 'selector' => '{{WRAPPER}} .ffp-filter-bar' )
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array( 'name' => 'filter_bar_shadow', 'selector' => '{{WRAPPER}} .ffp-filter-bar' )
		);

		$this->add_responsive_control(
			'filter_bar_padding',
			array(
				'label'      => __( 'Outer Padding', 'filterflow-posts' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem', '%' ),
				'default'    => array( 'top' => 18, 'right' => 24, 'bottom' => 18, 'left' => 24, 'unit' => 'px' ),
				'selectors'  => array( '{{WRAPPER}} .ffp-filter-bar' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'filter_bar_radius',
			array(
				'label'      => __( 'Border Radius', 'filterflow-posts' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'default'    => array( 'top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0, 'unit' => 'px' ),
				'selectors'  => array( '{{WRAPPER}} .ffp-filter-bar' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'filter_inner_max_width',
			array(
				'label'      => __( 'Inner Content Max Width', 'filterflow-posts' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vw' ),
				'range'      => array( 'px' => array( 'min' => 320, 'max' => 2400 ), 'vw' => array( 'min' => 50, 'max' => 100 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 1720 ),
				'selectors'  => array( '{{WRAPPER}} .ffp-filter-bar__inner' => 'max-width: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();
	}

	private function register_filter_style_controls(): void {
		$this->start_controls_section(
			'section_filter_style',
			array(
				'label' => __( 'Filter Chips', 'filterflow-posts' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'filter_visual_style',
			array(
				'label'        => __( 'Filter Style', 'filterflow-posts' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'pill',
				'options'      => array(
					'pill'       => __( 'Filled Pill', 'filterflow-posts' ),
					'outline'    => __( 'Outline', 'filterflow-posts' ),
					'underline'  => __( 'Underline Active', 'filterflow-posts' ),
					'overline'   => __( 'Overline Active', 'filterflow-posts' ),
					'doubleline' => __( 'Overline + Underline', 'filterflow-posts' ),
					'tabs'       => __( 'Modern Tabs', 'filterflow-posts' ),
					'minimal'    => __( 'Minimal Text', 'filterflow-posts' ),
				),
				'prefix_class' => 'ffp-filter-style-',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array( 'name' => 'filter_typography', 'selector' => '{{WRAPPER}} .ffp-chip, {{WRAPPER}} .ffp-mobile-filter-trigger, {{WRAPPER}} .ffp-tablet-select-trigger' )
		);


		$this->add_responsive_control(
			'filter_icon_size',
			array(
				'label'      => __( 'Icon Size', 'filterflow-posts' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array( 'min' => 10, 'max' => 40 ),
					'em' => array( 'min' => 0.6, 'max' => 2, 'step' => 0.05 ),
				),
				'default'    => array( 'unit' => 'px', 'size' => 17 ),
				'selectors'  => array( '{{WRAPPER}}' => '--ffp-filter-icon-size: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'filter_icon_gap',
			array(
				'label'      => __( 'Icon Gap', 'filterflow-posts' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 24 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 8 ),
				'selectors'  => array( '{{WRAPPER}}' => '--ffp-filter-icon-gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'filter_gap',
			array(
				'label'      => __( 'Gap', 'filterflow-posts' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 32 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 10 ),
				'selectors'  => array( '{{WRAPPER}} .ffp-filter-bar__inner, {{WRAPPER}} .ffp-mobile-controls, {{WRAPPER}} .ffp-mobile-primary, {{WRAPPER}} .ffp-mobile-quick-filters' => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'filter_min_height',
			array(
				'label'      => __( 'Minimum Height', 'filterflow-posts' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 30, 'max' => 90 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 42 ),
				'selectors'  => array( '{{WRAPPER}} .ffp-chip, {{WRAPPER}} .ffp-mobile-filter-trigger, {{WRAPPER}} .ffp-tablet-select-trigger' => 'min-height: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'filter_padding',
			array(
				'label'      => __( 'Padding', 'filterflow-posts' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'default'    => array( 'top' => 10, 'right' => 18, 'bottom' => 10, 'left' => 18, 'unit' => 'px' ),
				'selectors'  => array( '{{WRAPPER}} .ffp-chip, {{WRAPPER}} .ffp-mobile-filter-trigger, {{WRAPPER}} .ffp-tablet-select-trigger' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'filter_radius',
			array(
				'label'      => __( 'Border Radius', 'filterflow-posts' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 999 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 999 ),
				'selectors'  => array( '{{WRAPPER}}' => '--ffp-filter-radius: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'filter_indicator_heading',
			array(
				'label'     => __( 'Active Indicator', 'filterflow-posts' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array( 'filter_visual_style' => array( 'outline', 'underline', 'overline', 'doubleline', 'tabs', 'minimal' ) ),
			)
		);

		$this->add_control(
			'filter_indicator_color',
			array(
				'label'     => __( 'Indicator / Active Color', 'filterflow-posts' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#17345F',
				'selectors' => array( '{{WRAPPER}}' => '--ffp-filter-indicator: {{VALUE}};' ),
				'condition' => array( 'filter_visual_style' => array( 'outline', 'underline', 'overline', 'doubleline', 'tabs', 'minimal' ) ),
			)
		);

		$this->add_responsive_control(
			'filter_indicator_thickness',
			array(
				'label'      => __( 'Line Thickness', 'filterflow-posts' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 1, 'max' => 8 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 2 ),
				'selectors'  => array( '{{WRAPPER}}' => '--ffp-filter-indicator-size: {{SIZE}}{{UNIT}};' ),
				'condition'  => array( 'filter_visual_style' => array( 'underline', 'overline', 'doubleline', 'tabs' ) ),
			)
		);

		$this->add_responsive_control(
			'filter_indicator_width',
			array(
				'label'      => __( 'Line Width', 'filterflow-posts' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( '%' ),
				'range'      => array( '%' => array( 'min' => 10, 'max' => 100 ) ),
				'default'    => array( 'unit' => '%', 'size' => 100 ),
				'selectors'  => array( '{{WRAPPER}}' => '--ffp-filter-indicator-width: {{SIZE}}{{UNIT}};' ),
				'condition'  => array( 'filter_visual_style' => array( 'underline', 'overline', 'doubleline', 'tabs' ) ),
			)
		);

		$this->start_controls_tabs( 'filter_color_tabs' );
		$this->start_controls_tab( 'filter_normal_tab', array( 'label' => __( 'Normal', 'filterflow-posts' ) ) );
		$this->add_control( 'filter_text_color', array( 'label' => __( 'Text Color', 'filterflow-posts' ), 'type' => Controls_Manager::COLOR, 'default' => '#1B1F24', 'selectors' => array( '{{WRAPPER}}' => '--ffp-filter-text: {{VALUE}};' ) ) );
		$this->add_control( 'filter_background', array( 'label' => __( 'Background', 'filterflow-posts' ), 'type' => Controls_Manager::COLOR, 'default' => '#FFFFFF', 'selectors' => array( '{{WRAPPER}}' => '--ffp-filter-bg: {{VALUE}};' ) ) );
		$this->add_control( 'filter_border_color', array( 'label' => __( 'Border Color', 'filterflow-posts' ), 'type' => Controls_Manager::COLOR, 'default' => '#DDE1E7', 'selectors' => array( '{{WRAPPER}}' => '--ffp-filter-border-color: {{VALUE}};' ) ) );
		$this->end_controls_tab();

		$this->start_controls_tab( 'filter_hover_tab', array( 'label' => __( 'Hover', 'filterflow-posts' ) ) );
		$this->add_control( 'filter_hover_text', array( 'label' => __( 'Text Color', 'filterflow-posts' ), 'type' => Controls_Manager::COLOR, 'default' => '#1B1F24', 'selectors' => array( '{{WRAPPER}}' => '--ffp-filter-hover-text: {{VALUE}};' ) ) );
		$this->add_control( 'filter_hover_background', array( 'label' => __( 'Background', 'filterflow-posts' ), 'type' => Controls_Manager::COLOR, 'default' => '#F4F6F8', 'selectors' => array( '{{WRAPPER}}' => '--ffp-filter-hover-bg: {{VALUE}};' ) ) );
		$this->add_control( 'filter_hover_border', array( 'label' => __( 'Border Color', 'filterflow-posts' ), 'type' => Controls_Manager::COLOR, 'default' => '#B9C0CA', 'selectors' => array( '{{WRAPPER}}' => '--ffp-filter-hover-border: {{VALUE}};' ) ) );
		$this->end_controls_tab();

		$this->start_controls_tab( 'filter_active_tab', array( 'label' => __( 'Active', 'filterflow-posts' ) ) );
		$this->add_control( 'filter_active_text', array( 'label' => __( 'Text Color', 'filterflow-posts' ), 'type' => Controls_Manager::COLOR, 'default' => '#FFFFFF', 'selectors' => array( '{{WRAPPER}}' => '--ffp-filter-active-text: {{VALUE}};' ) ) );
		$this->add_control( 'filter_active_background', array( 'label' => __( 'Background', 'filterflow-posts' ), 'type' => Controls_Manager::COLOR, 'default' => '#2563EB', 'selectors' => array( '{{WRAPPER}}' => '--ffp-filter-active-bg: {{VALUE}};' ) ) );
		$this->add_control( 'filter_active_border', array( 'label' => __( 'Border Color', 'filterflow-posts' ), 'type' => Controls_Manager::COLOR, 'default' => '#2563EB', 'selectors' => array( '{{WRAPPER}}' => '--ffp-filter-active-border: {{VALUE}};' ) ) );
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_control(
			'mobile_trigger_heading',
			array(
				'label'     => __( 'Mobile Filter Button', 'filterflow-posts' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);
		$this->add_control( 'mobile_trigger_text_color', array( 'label' => __( 'Text & Icon Color', 'filterflow-posts' ), 'type' => Controls_Manager::COLOR, 'default' => '#FFFFFF', 'selectors' => array( '{{WRAPPER}}' => '--ffp-mobile-filter-text: {{VALUE}};' ) ) );
		$this->add_control( 'mobile_trigger_background', array( 'label' => __( 'Background', 'filterflow-posts' ), 'type' => Controls_Manager::COLOR, 'default' => '#07131C', 'selectors' => array( '{{WRAPPER}}' => '--ffp-mobile-filter-bg: {{VALUE}};' ) ) );
		$this->add_control( 'mobile_trigger_border_color', array( 'label' => __( 'Border Color', 'filterflow-posts' ), 'type' => Controls_Manager::COLOR, 'default' => '#07131C', 'selectors' => array( '{{WRAPPER}}' => '--ffp-mobile-filter-border: {{VALUE}};' ) ) );

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array( 'name' => 'filter_chip_shadow', 'selector' => '{{WRAPPER}} .ffp-chip, {{WRAPPER}} .ffp-mobile-filter-trigger, {{WRAPPER}} .ffp-tablet-select-trigger', 'separator' => 'before' )
		);

		$this->end_controls_section();
	}

	private function register_card_style_controls(): void {
		$this->start_controls_section(
			'section_card_style',
			array( 'label' => __( 'Cards', 'filterflow-posts' ), 'tab' => Controls_Manager::TAB_STYLE )
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array( 'name' => 'card_background', 'types' => array( 'classic', 'gradient' ), 'selector' => '{{WRAPPER}} .ffp-card' )
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array( 'name' => 'card_border', 'selector' => '{{WRAPPER}} .ffp-card' )
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array( 'name' => 'card_shadow', 'selector' => '{{WRAPPER}} .ffp-card' )
		);

		$this->add_control(
			'card_radius',
			array(
				'label'      => __( 'Border Radius', 'filterflow-posts' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'default'    => array( 'top' => 18, 'right' => 18, 'bottom' => 18, 'left' => 18, 'unit' => 'px' ),
				'selectors'  => array( '{{WRAPPER}} .ffp-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'content_padding',
			array(
				'label'      => __( 'Content Padding', 'filterflow-posts' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem' ),
				'default'    => array( 'top' => 22, 'right' => 22, 'bottom' => 22, 'left' => 22, 'unit' => 'px' ),
				'selectors'  => array( '{{WRAPPER}} .ffp-card__body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'image_ratio',
			array(
				'label'      => __( 'Image Aspect Ratio', 'filterflow-posts' ),
				'type'       => Controls_Manager::SELECT,
				'default'    => '16/9',
				'options'    => array( '16/9' => '16:9', '4/3' => '4:3', '3/2' => '3:2', '1/1' => '1:1', '21/9' => '21:9' ),
				'selectors'  => array( '{{WRAPPER}} .ffp-card__image' => 'aspect-ratio: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'image_fit',
			array(
				'label'     => __( 'Image Fit', 'filterflow-posts' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'cover',
				'options'   => array( 'cover' => __( 'Cover', 'filterflow-posts' ), 'contain' => __( 'Contain', 'filterflow-posts' ), 'fill' => __( 'Fill', 'filterflow-posts' ) ),
				'selectors' => array( '{{WRAPPER}} .ffp-card__image img' => 'object-fit: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'image_hover_zoom',
			array(
				'label'       => __( 'Image Hover Zoom', 'filterflow-posts' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 1.035,
				'min'         => 1,
				'max'         => 1.25,
				'step'        => 0.005,
				'description' => __( 'Use 1 for no zoom or up to 1.25 for a stronger effect.', 'filterflow-posts' ),
				'selectors'   => array( '{{WRAPPER}} .ffp-card:hover .ffp-card__image img' => 'transform: scale({{VALUE}});' ),
			)
		);

		$this->add_control(
			'card_hover_translate',
			array(
				'label'      => __( 'Hover Lift', 'filterflow-posts' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 20 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 4 ),
				'selectors'  => array( '{{WRAPPER}} .ffp-card:hover' => 'transform: translateY(-{{SIZE}}{{UNIT}});' ),
			)
		);

		$this->add_control( 'card_hover_border_color', array( 'label' => __( 'Hover Border Color', 'filterflow-posts' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .ffp-card:hover' => 'border-color: {{VALUE}};' ) ) );

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array( 'name' => 'card_hover_shadow', 'selector' => '{{WRAPPER}} .ffp-card:hover' )
		);

		$this->end_controls_section();
	}

	private function register_badge_style_controls(): void {
		$this->start_controls_section(
			'section_badge_style',
			array(
				'label' => __( 'Category Badges', 'filterflow-posts' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'badge_visual_style',
			array(
				'label'        => __( 'Badge Style', 'filterflow-posts' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'soft',
				'options'      => array(
					'soft'      => __( 'Soft Filled', 'filterflow-posts' ),
					'solid'     => __( 'Solid', 'filterflow-posts' ),
					'outline'   => __( 'Outline', 'filterflow-posts' ),
					'underline' => __( 'Underline', 'filterflow-posts' ),
					'text'      => __( 'Text Only', 'filterflow-posts' ),
				),
				'prefix_class' => 'ffp-badge-style-',
			)
		);

		$this->add_control(
			'badge_color_mode',
			array(
				'label'        => __( 'Color Mode', 'filterflow-posts' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'custom',
				'options'      => array(
					'custom'  => __( 'Custom Uniform Color', 'filterflow-posts' ),
					'palette' => __( 'Automatic Multi-Color Palette', 'filterflow-posts' ),
				),
				'prefix_class' => 'ffp-badge-colors-',
			)
		);

		$this->add_responsive_control(
			'badge_alignment',
			array(
				'label'     => __( 'Content Alignment', 'filterflow-posts' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array( 'title' => __( 'Left', 'filterflow-posts' ), 'icon' => 'eicon-h-align-left' ),
					'center'     => array( 'title' => __( 'Center', 'filterflow-posts' ), 'icon' => 'eicon-h-align-center' ),
					'flex-end'   => array( 'title' => __( 'Right', 'filterflow-posts' ), 'icon' => 'eicon-h-align-right' ),
				),
				'default'   => 'flex-start',
				'toggle'    => false,
				'selectors' => array( '{{WRAPPER}} .ffp-card__badges:not(.ffp-card__badges--overlay)' => 'justify-content: {{VALUE}};' ),
				'condition' => array( 'badge_position' => array( 'above-image', 'content' ) ),
			)
		);

		$this->add_responsive_control(
			'badge_overlay_inset',
			array(
				'label'      => __( 'Overlay Edge Offset', 'filterflow-posts' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 14 ),
				'selectors'  => array( '{{WRAPPER}} .ffp-card' => '--ffp-badge-overlay-inset: {{SIZE}}{{UNIT}};' ),
				'condition'  => array( 'badge_position!' => array( 'above-image', 'content' ) ),
			)
		);

		$this->add_responsive_control(
			'badge_above_image_padding',
			array(
				'label'      => __( 'Above-Image Container Padding', 'filterflow-posts' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem' ),
				'default'    => array( 'top' => 18, 'right' => 22, 'bottom' => 12, 'left' => 22, 'unit' => 'px' ),
				'selectors'  => array( '{{WRAPPER}} .ffp-card__badges--above-image' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
				'condition'  => array( 'badge_position' => 'above-image' ),
			)
		);

		$this->add_responsive_control(
			'badge_gap',
			array(
				'label'      => __( 'Horizontal Gap', 'filterflow-posts' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 6 ),
				'selectors'  => array( '{{WRAPPER}} .ffp-card__badges' => 'column-gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'badge_row_gap',
			array(
				'label'      => __( 'Row Gap', 'filterflow-posts' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 6 ),
				'selectors'  => array( '{{WRAPPER}} .ffp-card__badges' => 'row-gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'badge_margin',
			array(
				'label'      => __( 'Container Margin', 'filterflow-posts' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem' ),
				'default'    => array( 'top' => 0, 'right' => 0, 'bottom' => 10, 'left' => 0, 'unit' => 'px' ),
				'selectors'  => array( '{{WRAPPER}} .ffp-card__badges' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'badge_padding',
			array(
				'label'      => __( 'Badge Padding', 'filterflow-posts' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'default'    => array( 'top' => 5, 'right' => 9, 'bottom' => 5, 'left' => 9, 'unit' => 'px' ),
				'selectors'  => array( '{{WRAPPER}} .ffp-card__badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'badge_radius',
			array(
				'label'      => __( 'Border Radius', 'filterflow-posts' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'default'    => array( 'top' => 7, 'right' => 7, 'bottom' => 7, 'left' => 7, 'unit' => 'px' ),
				'selectors'  => array( '{{WRAPPER}} .ffp-card__badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array( 'name' => 'badge_typography', 'selector' => '{{WRAPPER}} .ffp-card__badge' )
		);

		$this->start_controls_tabs( 'badge_color_tabs' );
		$this->start_controls_tab( 'badge_normal_tab', array( 'label' => __( 'Normal', 'filterflow-posts' ) ) );
		$this->add_control( 'badge_color', array( 'label' => __( 'Text Color', 'filterflow-posts' ), 'type' => Controls_Manager::COLOR, 'default' => '#4057C9', 'selectors' => array( '{{WRAPPER}} .ffp-card__badge' => 'color: {{VALUE}};' ), 'condition' => array( 'badge_color_mode' => 'custom' ) ) );
		$this->add_control( 'badge_background', array( 'label' => __( 'Background', 'filterflow-posts' ), 'type' => Controls_Manager::COLOR, 'default' => '#EEF0FF', 'selectors' => array( '{{WRAPPER}} .ffp-card__badge' => 'background-color: {{VALUE}};' ), 'condition' => array( 'badge_color_mode' => 'custom' ) ) );
		$this->add_control( 'badge_border_color', array( 'label' => __( 'Border Color', 'filterflow-posts' ), 'type' => Controls_Manager::COLOR, 'default' => 'rgba(64,87,201,0.12)', 'selectors' => array( '{{WRAPPER}} .ffp-card__badge' => 'border-color: {{VALUE}};' ), 'condition' => array( 'badge_color_mode' => 'custom' ) ) );
		$this->end_controls_tab();

		$this->start_controls_tab( 'badge_hover_tab', array( 'label' => __( 'Hover', 'filterflow-posts' ) ) );
		$this->add_control( 'badge_hover_color', array( 'label' => __( 'Text Color', 'filterflow-posts' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .ffp-card__badge:hover' => 'color: {{VALUE}};' ), 'condition' => array( 'badge_color_mode' => 'custom' ) ) );
		$this->add_control( 'badge_hover_background', array( 'label' => __( 'Background', 'filterflow-posts' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .ffp-card__badge:hover' => 'background-color: {{VALUE}};' ), 'condition' => array( 'badge_color_mode' => 'custom' ) ) );
		$this->add_control( 'badge_hover_border_color', array( 'label' => __( 'Border Color', 'filterflow-posts' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .ffp-card__badge:hover' => 'border-color: {{VALUE}};' ), 'condition' => array( 'badge_color_mode' => 'custom' ) ) );
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array( 'name' => 'badge_border', 'selector' => '{{WRAPPER}} .ffp-card__badge', 'separator' => 'before' )
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array( 'name' => 'badge_shadow', 'selector' => '{{WRAPPER}} .ffp-card__badge' )
		);

		$this->add_responsive_control(
			'badge_hover_lift',
			array(
				'label'      => __( 'Hover Lift', 'filterflow-posts' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => -10, 'max' => 0 ) ),
				'default'    => array( 'unit' => 'px', 'size' => -1 ),
				'selectors'  => array( '{{WRAPPER}} .ffp-card__badge:hover' => 'transform: translateY({{SIZE}}{{UNIT}});' ),
			)
		);

		$this->end_controls_section();
	}


	private function register_content_style_controls(): void {
		$this->start_controls_section(
			'section_content_style',
			array( 'label' => __( 'Typography & Colors', 'filterflow-posts' ), 'tab' => Controls_Manager::TAB_STYLE )
		);

		$this->add_control(
			'intro_heading_style_heading',
			array(
				'label' => __( 'Widget Heading', 'filterflow-posts' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'heading_color',
			array(
				'label'     => __( 'Color', 'filterflow-posts' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#101419',
				'selectors' => array( '{{WRAPPER}} .ffp-intro__title' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array( 'name' => 'heading_typography', 'selector' => '{{WRAPPER}} .ffp-intro__title' )
		);

		$this->add_control(
			'heading_weight_override',
			array(
				'label'       => __( 'Font Weight Override', 'filterflow-posts' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => array(
					''    => __( 'Use Typography Setting', 'filterflow-posts' ),
					'400' => __( 'Regular 400', 'filterflow-posts' ),
					'500' => __( 'Medium 500', 'filterflow-posts' ),
					'600' => __( 'Semi Bold 600', 'filterflow-posts' ),
					'700' => __( 'Bold 700', 'filterflow-posts' ),
					'800' => __( 'Extra Bold 800', 'filterflow-posts' ),
					'900' => __( 'Black 900', 'filterflow-posts' ),
				),
				'selectors'   => array( '{{WRAPPER}} .ffp-intro__title' => 'font-weight: {{VALUE}};' ),
				'description' => __( 'Typography already includes font weight; this provides a quick explicit override.', 'filterflow-posts' ),
			)
		);

		$this->add_control(
			'heading_decoration',
			array(
				'label'     => __( 'Text Decoration', 'filterflow-posts' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'none',
				'options'   => array(
					'none'         => __( 'None', 'filterflow-posts' ),
					'underline'    => __( 'Underline', 'filterflow-posts' ),
					'overline'     => __( 'Overline', 'filterflow-posts' ),
					'line-through' => __( 'Line Through', 'filterflow-posts' ),
				),
				'selectors' => array( '{{WRAPPER}} .ffp-intro__title' => 'text-decoration-line: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'heading_decoration_color',
			array(
				'label'     => __( 'Decoration Color', 'filterflow-posts' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .ffp-intro__title' => 'text-decoration-color: {{VALUE}};' ),
				'condition' => array( 'heading_decoration!' => 'none' ),
			)
		);

		$this->add_control(
			'description_style_heading',
			array(
				'label'     => __( 'Widget Description', 'filterflow-posts' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control( 'description_color', array( 'label' => __( 'Color', 'filterflow-posts' ), 'type' => Controls_Manager::COLOR, 'default' => '#69707A', 'selectors' => array( '{{WRAPPER}} .ffp-intro__description' => 'color: {{VALUE}};' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'description_typography', 'selector' => '{{WRAPPER}} .ffp-intro__description' ) );

		$this->add_control(
			'title_heading',
			array(
				'label'     => __( 'Post Title', 'filterflow-posts' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array( 'name' => 'title_typography', 'selector' => '{{WRAPPER}} .ffp-card__title, {{WRAPPER}} .ffp-card__title a' )
		);

		$this->add_control(
			'title_weight_override',
			array(
				'label'     => __( 'Font Weight Override', 'filterflow-posts' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => array(
					''    => __( 'Use Typography Setting', 'filterflow-posts' ),
					'400' => __( 'Regular 400', 'filterflow-posts' ),
					'500' => __( 'Medium 500', 'filterflow-posts' ),
					'600' => __( 'Semi Bold 600', 'filterflow-posts' ),
					'700' => __( 'Bold 700', 'filterflow-posts' ),
					'800' => __( 'Extra Bold 800', 'filterflow-posts' ),
					'900' => __( 'Black 900', 'filterflow-posts' ),
				),
				'selectors' => array( '{{WRAPPER}} .ffp-card__title, {{WRAPPER}} .ffp-card__title a' => 'font-weight: {{VALUE}};' ),
			)
		);

		$this->start_controls_tabs( 'post_title_state_tabs' );

		$this->start_controls_tab(
			'post_title_normal_tab',
			array( 'label' => __( 'Normal', 'filterflow-posts' ) )
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'Color', 'filterflow-posts' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#12171C',
				'selectors' => array( '{{WRAPPER}} .ffp-card__title a' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'title_normal_decoration',
			array(
				'label'     => __( 'Text Decoration', 'filterflow-posts' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'none',
				'options'   => array(
					'none'         => __( 'None', 'filterflow-posts' ),
					'underline'    => __( 'Underline', 'filterflow-posts' ),
					'overline'     => __( 'Overline', 'filterflow-posts' ),
					'line-through' => __( 'Line Through', 'filterflow-posts' ),
				),
				'selectors' => array( '{{WRAPPER}} .ffp-card__title a' => 'text-decoration-line: {{VALUE}};' ),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'post_title_hover_tab',
			array( 'label' => __( 'Hover', 'filterflow-posts' ) )
		);

		$this->add_control(
			'title_hover_color',
			array(
				'label'     => __( 'Color', 'filterflow-posts' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .ffp-card__title a:hover, {{WRAPPER}} .ffp-card__title a:focus-visible' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'title_hover_decoration',
			array(
				'label'     => __( 'Text Decoration', 'filterflow-posts' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'none',
				'options'   => array(
					'none'         => __( 'None', 'filterflow-posts' ),
					'underline'    => __( 'Underline', 'filterflow-posts' ),
					'overline'     => __( 'Overline', 'filterflow-posts' ),
					'line-through' => __( 'Line Through', 'filterflow-posts' ),
				),
				'selectors' => array( '{{WRAPPER}} .ffp-card__title a:hover, {{WRAPPER}} .ffp-card__title a:focus-visible' => 'text-decoration-line: {{VALUE}};' ),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_control(
			'title_decoration_heading',
			array(
				'label'     => __( 'Decoration Details', 'filterflow-posts' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'title_decoration_color',
			array(
				'label'     => __( 'Decoration Color', 'filterflow-posts' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .ffp-card__title a' => 'text-decoration-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'title_decoration_style',
			array(
				'label'     => __( 'Decoration Style', 'filterflow-posts' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'solid',
				'options'   => array(
					'solid'  => __( 'Solid', 'filterflow-posts' ),
					'double' => __( 'Double', 'filterflow-posts' ),
					'dotted' => __( 'Dotted', 'filterflow-posts' ),
					'dashed' => __( 'Dashed', 'filterflow-posts' ),
					'wavy'   => __( 'Wavy', 'filterflow-posts' ),
				),
				'selectors' => array( '{{WRAPPER}} .ffp-card__title a' => 'text-decoration-style: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'title_decoration_thickness',
			array(
				'label'      => __( 'Decoration Thickness', 'filterflow-posts' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array( 'px' => array( 'min' => 1, 'max' => 10 ), 'em' => array( 'min' => 0.03, 'max' => 0.3, 'step' => 0.01 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 1 ),
				'selectors'  => array( '{{WRAPPER}} .ffp-card__title a' => 'text-decoration-thickness: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'title_decoration_offset',
			array(
				'label'      => __( 'Underline Offset', 'filterflow-posts' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 16 ), 'em' => array( 'min' => 0, 'max' => 0.5, 'step' => 0.01 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 3 ),
				'selectors'  => array( '{{WRAPPER}} .ffp-card__title a' => 'text-underline-offset: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'title_bottom_spacing',
			array(
				'label'      => __( 'Bottom Spacing', 'filterflow-posts' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors'  => array( '{{WRAPPER}} .ffp-card__title' => 'margin-bottom: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'title_transition_duration',
			array(
				'label'      => __( 'Hover Transition (ms)', 'filterflow-posts' ),
				'type'       => Controls_Manager::NUMBER,
				'default'    => 180,
				'min'        => 0,
				'max'        => 2000,
				'step'       => 10,
				'selectors'  => array( '{{WRAPPER}} .ffp-card__title a' => 'transition-duration: {{VALUE}}ms;' ),
			)
		);

		$this->add_control( 'excerpt_heading', array( 'label' => __( 'Excerpt', 'filterflow-posts' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );
		$this->add_control( 'excerpt_color', array( 'label' => __( 'Color', 'filterflow-posts' ), 'type' => Controls_Manager::COLOR, 'default' => '#626A74', 'selectors' => array( '{{WRAPPER}} .ffp-card__excerpt' => 'color: {{VALUE}};' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'excerpt_typography', 'selector' => '{{WRAPPER}} .ffp-card__excerpt' ) );

		$this->add_control( 'meta_heading', array( 'label' => __( 'Meta', 'filterflow-posts' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );
		$this->add_control( 'meta_color', array( 'label' => __( 'Color', 'filterflow-posts' ), 'type' => Controls_Manager::COLOR, 'default' => '#7A818B', 'selectors' => array( '{{WRAPPER}} .ffp-card__meta, {{WRAPPER}} .ffp-card__meta a, {{WRAPPER}} .ffp-card__arrow' => 'color: {{VALUE}};' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'meta_typography', 'selector' => '{{WRAPPER}} .ffp-card__meta' ) );
		$this->add_responsive_control( 'meta_gap', array( 'label' => __( 'Meta Item Gap', 'filterflow-posts' ), 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'px', 'em' ), 'range' => array( 'px' => array( 'min' => 0, 'max' => 40 ) ), 'default' => array( 'unit' => 'px', 'size' => 10 ), 'selectors' => array( '{{WRAPPER}} .ffp-card__meta' => 'column-gap: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_control( 'author_avatar_radius', array( 'label' => __( 'Author Avatar Radius', 'filterflow-posts' ), 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'px', '%' ), 'range' => array( 'px' => array( 'min' => 0, 'max' => 64 ), '%' => array( 'min' => 0, 'max' => 50 ) ), 'default' => array( 'unit' => '%', 'size' => 50 ), 'selectors' => array( '{{WRAPPER}} .ffp-card__author-avatar' => 'border-radius: {{SIZE}}{{UNIT}};' ) ) );

		$this->end_controls_section();
	}

	private function register_pagination_style_controls(): void {
		$this->start_controls_section(
			'section_pagination_style',
			array( 'label' => __( 'Pagination', 'filterflow-posts' ), 'tab' => Controls_Manager::TAB_STYLE )
		);

		$this->add_responsive_control(
			'pagination_alignment',
			array(
				'label'     => __( 'Alignment', 'filterflow-posts' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array( 'title' => __( 'Left', 'filterflow-posts' ), 'icon' => 'eicon-h-align-left' ),
					'center'     => array( 'title' => __( 'Center', 'filterflow-posts' ), 'icon' => 'eicon-h-align-center' ),
					'flex-end'   => array( 'title' => __( 'Right', 'filterflow-posts' ), 'icon' => 'eicon-h-align-right' ),
				),
				'default'   => 'center',
				'toggle'    => false,
				'selectors' => array( '{{WRAPPER}} .ffp-pagination' => 'justify-content: {{VALUE}};' ),
			)
		);

		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'pagination_typography', 'selector' => '{{WRAPPER}} .ffp-page, {{WRAPPER}} .ffp-load-more' ) );
		$this->add_control( 'pagination_color', array( 'label' => __( 'Text Color', 'filterflow-posts' ), 'type' => Controls_Manager::COLOR, 'default' => '#172029', 'selectors' => array( '{{WRAPPER}} .ffp-page, {{WRAPPER}} .ffp-load-more' => 'color: {{VALUE}};' ) ) );
		$this->add_control( 'pagination_background', array( 'label' => __( 'Background', 'filterflow-posts' ), 'type' => Controls_Manager::COLOR, 'default' => '#FFFFFF', 'selectors' => array( '{{WRAPPER}} .ffp-page, {{WRAPPER}} .ffp-load-more' => 'background-color: {{VALUE}};' ) ) );
		$this->add_control( 'pagination_active_color', array( 'label' => __( 'Active Text', 'filterflow-posts' ), 'type' => Controls_Manager::COLOR, 'default' => '#FFFFFF', 'selectors' => array( '{{WRAPPER}} .ffp-page.is-active' => 'color: {{VALUE}};' ) ) );
		$this->add_control( 'pagination_active_background', array( 'label' => __( 'Active Background', 'filterflow-posts' ), 'type' => Controls_Manager::COLOR, 'default' => '#07131C', 'selectors' => array( '{{WRAPPER}} .ffp-page.is-active, {{WRAPPER}} .ffp-load-more:hover' => 'background-color: {{VALUE}}; border-color: {{VALUE}};' ) ) );

		$this->end_controls_section();
	}

	private function switcher_control( string $label, string $default = 'yes' ): array {
		return array(
			'label'        => $label,
			'type'         => Controls_Manager::SWITCHER,
			'label_on'     => __( 'Show', 'filterflow-posts' ),
			'label_off'    => __( 'Hide', 'filterflow-posts' ),
			'return_value' => 'yes',
			'default'      => $default,
		);
	}

	private function get_category_options(): array {
		$options    = array();
		$categories = get_categories( array( 'hide_empty' => false ) );

		foreach ( $categories as $category ) {
			$options[ $category->term_id ] = $category->name;
		}

		return $options;
	}


	private function sanitize_category_ids( $value ): array {
		$ids = array();

		foreach ( is_array( $value ) ? $value : array() as $category_id ) {
			if ( ! is_scalar( $category_id ) ) {
				continue;
			}

			$category_id = absint( $category_id );
			if ( $category_id ) {
				$ids[] = $category_id;
			}
		}

		return array_slice( array_values( array_unique( $ids ) ), 0, 200 );
	}

	private function get_filter_terms( array $settings ): array {
		$allowed_orderby = array( 'name', 'count', 'id' );
		$requested_orderby = is_scalar( $settings['filter_orderby'] ?? null ) ? (string) $settings['filter_orderby'] : 'name';
		$requested_order = is_scalar( $settings['filter_order'] ?? null ) ? strtoupper( (string) $settings['filter_order'] ) : 'ASC';

		$args = array(
			'taxonomy'   => 'category',
			'hide_empty' => true,
			'orderby'    => in_array( $requested_orderby, $allowed_orderby, true ) ? $requested_orderby : 'name',
			'order'      => in_array( $requested_order, array( 'ASC', 'DESC' ), true ) ? $requested_order : 'ASC',
		);

		$category_ids = $this->sanitize_category_ids( $settings['categories'] ?? array() );

		if ( ! empty( $category_ids ) ) {
			$args['include'] = $category_ids;
		}

		$terms = get_terms( $args );
		return is_wp_error( $terms ) ? array() : $terms;
	}

	/**
	 * Build filter color custom properties directly from widget settings.
	 *
	 * Elementor's editor can temporarily retain an old prefix class while a
	 * select control is being changed. Keeping the selected colors on the
	 * widget itself makes the preview deterministic and also avoids theme
	 * button rules overriding the Filled Pill state.
	 *
	 * @param array<string,mixed> $settings Widget settings.
	 * @return string
	 */
	private function get_filter_color_properties( array $settings ): string {
		$globals = isset( $settings['__globals__'] ) && is_array( $settings['__globals__'] ) ? $settings['__globals__'] : array();
		$map     = array(
			'filter_text_color'           => '--ffp-filter-text',
			'filter_background'           => '--ffp-filter-bg',
			'filter_border_color'         => '--ffp-filter-border-color',
			'filter_hover_text'           => '--ffp-filter-hover-text',
			'filter_hover_background'     => '--ffp-filter-hover-bg',
			'filter_hover_border'         => '--ffp-filter-hover-border',
			'filter_active_text'          => '--ffp-filter-active-text',
			'filter_active_background'    => '--ffp-filter-active-bg',
			'filter_active_border'        => '--ffp-filter-active-border',
			'mobile_trigger_text_color'   => '--ffp-mobile-filter-text',
			'mobile_trigger_background'   => '--ffp-mobile-filter-bg',
			'mobile_trigger_border_color' => '--ffp-mobile-filter-border',
		);
		$rules   = array();

		foreach ( $map as $setting_key => $property ) {
			// Let Elementor resolve global colors through its generated CSS.
			if ( ! empty( $globals[ $setting_key ] ) ) {
				continue;
			}

			if ( ! isset( $settings[ $setting_key ] ) || ! is_scalar( $settings[ $setting_key ] ) ) {
				continue;
			}

			$value = trim( (string) $settings[ $setting_key ] );
			if ( '' === $value || preg_match( '/[;{}<>]/', $value ) ) {
				continue;
			}

			$rules[] = $property . ':' . $value;
		}

		if ( empty( $globals['filter_radius'] ) && isset( $settings['filter_radius'] ) && is_array( $settings['filter_radius'] ) ) {
			$radius_size = isset( $settings['filter_radius']['size'] ) && is_numeric( $settings['filter_radius']['size'] ) ? (float) $settings['filter_radius']['size'] : null;
			$radius_unit = isset( $settings['filter_radius']['unit'] ) && in_array( $settings['filter_radius']['unit'], array( 'px', 'em', 'rem', '%' ), true ) ? $settings['filter_radius']['unit'] : 'px';
			if ( null !== $radius_size ) {
				$rules[] = '--ffp-filter-radius:' . $radius_size . $radius_unit;
			}
		}

		return implode( ';', $rules );
	}

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$terms    = $this->get_filter_terms( $settings );
		$widget_id = 'ffp-' . $this->get_id();

		$query_settings = Renderer::sanitize_settings(
			array(
				'categories'         => $this->sanitize_category_ids( $settings['categories'] ?? array() ),
				'posts_per_page'     => $settings['posts_per_page'] ?? 6,
				'orderby'            => $settings['orderby'] ?? 'date',
				'order'              => $settings['order'] ?? 'DESC',
				'ignore_sticky'      => 'yes' === ( $settings['ignore_sticky'] ?? '' ),
				'exclude_current'    => 'yes' === ( $settings['exclude_current'] ?? '' ),
				'current_post_id'    => is_singular( 'post' ) ? get_queried_object_id() : 0,
				'show_image'         => 'yes' === ( $settings['show_image'] ?? '' ),
				'image_size'         => $settings['image_size'] ?? 'large',
				'show_badge'         => 'yes' === ( $settings['show_badge'] ?? '' ),
				'link_badges'        => 'yes' === ( $settings['link_badges'] ?? '' ),
				'badge_limit'        => $settings['badge_limit'] ?? 0,
				'badge_position'     => $settings['badge_position'] ?? 'content',
				'card_title_tag'     => $settings['card_title_tag'] ?? 'h3',
				'show_excerpt'       => 'yes' === ( $settings['show_excerpt'] ?? '' ),
				'excerpt_source'     => $settings['excerpt_source'] ?? 'smart',
				'excerpt_length'     => $settings['excerpt_length'] ?? 20,
				'show_author'        => 'yes' === ( $settings['show_author'] ?? '' ),
				'author_prefix'      => $settings['author_prefix'] ?? __( 'By', 'filterflow-posts' ),
				'show_author_avatar' => 'yes' === ( $settings['show_author_avatar'] ?? '' ),
				'author_avatar_size' => $settings['author_avatar_size'] ?? 22,
				'link_author'        => 'yes' === ( $settings['link_author'] ?? '' ),
				'show_date'          => 'yes' === ( $settings['show_date'] ?? '' ),
				'date_source'        => $settings['date_source'] ?? 'published',
				'date_format'        => $settings['date_format'] ?? '',
				'show_reading_time'  => 'yes' === ( $settings['show_reading_time'] ?? '' ),
				'words_per_minute'   => $settings['words_per_minute'] ?? 220,
				'show_comments'      => 'yes' === ( $settings['show_comments'] ?? '' ),
				'show_arrow'         => 'yes' === ( $settings['show_arrow'] ?? '' ),
				'pagination_type'    => $settings['pagination_type'] ?? 'numbers',
				'load_more_label'    => $settings['load_more_label'] ?? __( 'Load more', 'filterflow-posts' ),
				'no_posts_message'   => $settings['no_posts_message'] ?? __( 'No posts found.', 'filterflow-posts' ),
				'open_new_tab'       => 'yes' === ( $settings['open_new_tab'] ?? '' ),
			)
		);

		$query = Renderer::get_query( $query_settings, 0, 1 );

		$filter_style = sanitize_key( is_scalar( $settings['filter_visual_style'] ?? null ) ? (string) $settings['filter_visual_style'] : 'pill' );
		$badge_style  = sanitize_key( is_scalar( $settings['badge_visual_style'] ?? null ) ? (string) $settings['badge_visual_style'] : 'soft' );
		$badge_colors = sanitize_key( is_scalar( $settings['badge_color_mode'] ?? null ) ? (string) $settings['badge_color_mode'] : 'custom' );
		$show_filter_icons = 'yes' === ( $settings['show_filter_icons'] ?? '' );
		$category_icons = $show_filter_icons ? $this->get_category_icon_map( $settings['category_filter_icons'] ?? array() ) : array();
		$all_filter_icon = $show_filter_icons ? $this->resolve_filter_icon( is_array( $settings['all_filter_icon'] ?? null ) ? $settings['all_filter_icon'] : array(), null, true ) : array();
		$resolved_category_icons = array();
		if ( $show_filter_icons ) {
			foreach ( $terms as $icon_term ) {
				$resolved_category_icons[ (int) $icon_term->term_id ] = $this->resolve_filter_icon( $category_icons[ (int) $icon_term->term_id ] ?? array(), $icon_term );
			}
		}

		$this->add_render_attribute(
			'wrapper',
			array(
				'id'                          => $widget_id,
				'class'                       => array(
					'ffp-widget',
					'ffp-filter-style-' . sanitize_html_class( $filter_style ),
					'ffp-badge-style-' . sanitize_html_class( $badge_style ),
					'ffp-badge-colors-' . sanitize_html_class( $badge_colors ),
				),
				'data-settings'               => wp_json_encode( $query_settings ),
				'data-tablet-visible-filters' => max( 1, absint( is_scalar( $settings['tablet_visible_filters'] ?? null ) ? $settings['tablet_visible_filters'] : 6 ) ),
				'data-mobile-visible-filters' => absint( is_scalar( $settings['mobile_visible_filters'] ?? null ) ? $settings['mobile_visible_filters'] : 3 ),
				'data-tablet-filter-layout'   => sanitize_key( is_scalar( $settings['tablet_filter_layout'] ?? null ) ? (string) $settings['tablet_filter_layout'] : 'select' ),
				'data-mobile-filter-layout'   => sanitize_key( is_scalar( $settings['mobile_filter_layout'] ?? null ) ? (string) $settings['mobile_filter_layout'] : 'fixed-all' ),
				'data-tablet-breakpoint'      => min( 1600, max( 700, absint( is_scalar( $settings['filter_tablet_breakpoint'] ?? null ) ? $settings['filter_tablet_breakpoint'] : 1024 ) ) ),
				'data-mobile-breakpoint'      => min( 1024, max( 320, absint( is_scalar( $settings['filter_mobile_breakpoint'] ?? null ) ? $settings['filter_mobile_breakpoint'] : 767 ) ) ),
				'data-mobile-active-prefix'   => sanitize_text_field( is_scalar( $settings['mobile_active_prefix'] ?? null ) ? (string) $settings['mobile_active_prefix'] : '' ),
				'data-header-collision-guard' => 'yes' === ( $settings['prevent_header_overlap'] ?? 'yes' ) ? 'yes' : 'no',
				'data-header-collision-gap'   => min( 120, max( 0, absint( is_scalar( $settings['header_collision_gap'] ?? null ) ? $settings['header_collision_gap'] : 16 ) ) ),
				'data-all-label'              => sanitize_text_field( is_scalar( $settings['all_label'] ?? null ) ? (string) $settings['all_label'] : __( 'All', 'filterflow-posts' ) ),
			)
		);

		$filter_color_properties = $this->get_filter_color_properties( $settings );
		if ( '' !== $filter_color_properties ) {
			$this->add_render_attribute( 'wrapper', 'style', $filter_color_properties );
		}
		?>
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php if ( 'yes' === ( $settings['show_intro'] ?? '' ) ) : ?>
				<div class="ffp-intro">
					<?php
					$allowed_tags  = array( 'h1', 'h2', 'h3', 'h4', 'div' );
					$requested_tag = is_scalar( $settings['heading_tag'] ?? null ) ? (string) $settings['heading_tag'] : 'h2';
					$heading_tag   = in_array( $requested_tag, $allowed_tags, true ) ? $requested_tag : 'h2';
					printf( '<%1$s class="ffp-intro__title">%2$s</%1$s>', esc_attr( $heading_tag ), esc_html( $settings['heading'] ?? '' ) );
					?>
					<?php if ( ! empty( $settings['description'] ) ) : ?>
						<div class="ffp-intro__description"><?php echo esc_html( $settings['description'] ); ?></div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( 'yes' === ( $settings['show_filters'] ?? '' ) && ! empty( $terms ) ) : ?>
				<div class="ffp-filter-bar">
					<div class="ffp-filter-bar__inner">
						<div class="ffp-responsive-filter-controls">
							<button type="button" class="ffp-tablet-select-trigger" aria-haspopup="dialog" aria-controls="<?php echo esc_attr( $widget_id . '-sheet' ); ?>" aria-expanded="false">
								<span class="ffp-tablet-select-main">
									<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 7h10M18 7h2M14 5v4M4 17h2M10 17h10M8 15v4M4 12h4M12 12h8M10 10v4"/></svg>
									<span class="ffp-tablet-select-copy">
										<span class="ffp-tablet-select-caption"><?php echo esc_html( $settings['mobile_filter_label'] ?? __( 'Filter', 'filterflow-posts' ) ); ?></span>
										<span class="ffp-tablet-active-label"><?php echo esc_html( $settings['all_label'] ?? __( 'All', 'filterflow-posts' ) ); ?></span>
									</span>
								</span>
								<svg class="ffp-tablet-select-chevron" aria-hidden="true" viewBox="0 0 24 24"><path d="m7 10 5 5 5-5"/></svg>
							</button>

							<div class="ffp-mobile-controls">
								<div class="ffp-mobile-primary">
									<button type="button" class="ffp-mobile-filter-trigger" aria-haspopup="dialog" aria-controls="<?php echo esc_attr( $widget_id . '-sheet' ); ?>" aria-expanded="false">
										<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 7h10M18 7h2M14 5v4M4 17h2M10 17h10M8 15v4M4 12h4M12 12h8M10 10v4"/></svg>
										<span><?php echo esc_html( $settings['mobile_filter_label'] ?? __( 'Filter', 'filterflow-posts' ) ); ?></span>
									</button>
									<?php $this->render_filter_button( 0, $settings['all_label'] ?? __( 'All', 'filterflow-posts' ), true, 'ffp-mobile-all-trigger', -1, -1, false, $all_filter_icon ); ?>
								</div>
								<div class="ffp-mobile-quick-filters" role="group" aria-label="<?php echo esc_attr__( 'Quick category filters', 'filterflow-posts' ); ?>">
									<?php $this->render_filter_button( 0, $settings['all_label'] ?? __( 'All', 'filterflow-posts' ), true, 'ffp-mobile-quick-chip ffp-mobile-quick-all', -1, -1, false, $all_filter_icon ); ?>
									<?php foreach ( $terms as $index => $term ) : ?>
										<?php $this->render_filter_button( (int) $term->term_id, $term->name, false, 'ffp-mobile-quick-chip', $index, (int) $term->count, 'yes' === ( $settings['show_filter_counts'] ?? '' ), $resolved_category_icons[ (int) $term->term_id ] ?? array() ); ?>
									<?php endforeach; ?>
								</div>
							</div>
						</div>

						<div class="ffp-filter-chips" role="group" aria-label="<?php echo esc_attr__( 'Filter posts by category', 'filterflow-posts' ); ?>">
							<?php $show_filter_counts = 'yes' === ( $settings['show_filter_counts'] ?? '' ); ?>
							<?php $this->render_filter_button( 0, $settings['all_label'] ?? __( 'All', 'filterflow-posts' ), true, 'ffp-chip--all', -1, -1, false, $all_filter_icon ); ?>
							<?php foreach ( $terms as $index => $term ) : ?>
								<?php $this->render_filter_button( (int) $term->term_id, $term->name, false, 'ffp-chip--term', $index, (int) $term->count, $show_filter_counts, $resolved_category_icons[ (int) $term->term_id ] ?? array() ); ?>
							<?php endforeach; ?>

							<button type="button" class="ffp-chip ffp-more-trigger" aria-expanded="false" aria-haspopup="menu" aria-controls="<?php echo esc_attr( $widget_id . '-overflow-menu' ); ?>">
								<span><?php echo esc_html( $settings['more_label'] ?? __( 'More', 'filterflow-posts' ) ); ?></span>
								<svg aria-hidden="true" viewBox="0 0 24 24"><path d="m7 10 5 5 5-5"/></svg>
							</button>

							<div id="<?php echo esc_attr( $widget_id . '-overflow-menu' ); ?>" class="ffp-overflow-menu" role="menu" hidden></div>
						</div>
					</div>
				</div>

				<div class="ffp-sheet-backdrop" hidden></div>
				<div id="<?php echo esc_attr( $widget_id . '-sheet' ); ?>" class="ffp-sheet" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $widget_id . '-sheet-title' ); ?>" hidden>
					<div class="ffp-sheet__handle" aria-hidden="true"></div>
					<div class="ffp-sheet__header">
						<h3 id="<?php echo esc_attr( $widget_id . '-sheet-title' ); ?>"><?php echo esc_html( $settings['mobile_sheet_title'] ?? __( 'Filter Topics', 'filterflow-posts' ) ); ?></h3>
						<button type="button" class="ffp-sheet__close" aria-label="<?php echo esc_attr__( 'Close filters', 'filterflow-posts' ); ?>">×</button>
					</div>
					<div class="ffp-sheet__options">
						<?php $this->render_sheet_option( 0, $settings['all_label'] ?? __( 'All', 'filterflow-posts' ), true, $widget_id, -1, false, $all_filter_icon ); ?>
						<?php foreach ( $terms as $term ) : ?>
							<?php $this->render_sheet_option( (int) $term->term_id, $term->name, false, $widget_id, (int) $term->count, 'yes' === ( $settings['show_filter_counts'] ?? '' ), $resolved_category_icons[ (int) $term->term_id ] ?? array() ); ?>
						<?php endforeach; ?>
					</div>
					<div class="ffp-sheet__actions">
						<button type="button" class="ffp-sheet__apply"><?php echo esc_html( $settings['mobile_apply_label'] ?? __( 'Apply Filter', 'filterflow-posts' ) ); ?></button>
						<button type="button" class="ffp-sheet__clear"><?php echo esc_html( $settings['mobile_clear_label'] ?? __( 'Clear Filter', 'filterflow-posts' ) ); ?></button>
					</div>
				</div>
			<?php endif; ?>

			<div class="ffp-results" aria-live="polite" aria-busy="false">
				<div class="ffp-grid"><?php echo Renderer::render_posts( $query, $query_settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<div class="ffp-pagination"><?php echo Renderer::render_pagination( $query, $query_settings, 1 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			</div>
			<div class="ffp-status screen-reader-text" aria-live="polite"></div>
		</div>
		<?php
	}

	private function render_filter_button( int $term_id, string $label, bool $active = false, string $class = '', int $index = -1, int $count = -1, bool $show_count = false, array $icon = array() ): void {
		$count_html = $show_count && $count >= 0 ? '<span class="ffp-chip__count">' . esc_html( number_format_i18n( $count ) ) . '</span>' : '';
		$icon_html  = $this->get_filter_icon_html( $icon );
		printf(
			'<button type="button" class="ffp-chip %1$s%2$s" data-term="%3$s" data-term-index="%4$s" data-label="%5$s" aria-pressed="%6$s">%7$s<span class="ffp-chip__label">%8$s</span>%9$s</button>',
			esc_attr( $class ),
			$active ? ' is-active' : '',
			esc_attr( (string) $term_id ),
			esc_attr( (string) $index ),
			esc_attr( $label ),
			$active ? 'true' : 'false',
			wp_kses( $icon_html, $this->get_filter_icon_allowed_html() ),
			esc_html( $label ),
			wp_kses_post( $count_html )
		);
	}

	private function render_sheet_option( int $term_id, string $label, bool $checked, string $widget_id, int $count = -1, bool $show_count = false, array $icon = array() ): void {
		$input_id  = $widget_id . '-term-' . $term_id;
		$icon_html = $this->get_filter_icon_html( $icon );
		?>
		<label class="ffp-sheet__option" for="<?php echo esc_attr( $input_id ); ?>">
			<span class="ffp-sheet__option-label"><?php echo wp_kses( $icon_html, $this->get_filter_icon_allowed_html() ); ?><span><?php echo esc_html( $label ); ?></span><?php if ( $show_count && $count >= 0 ) : ?><span class="ffp-sheet__count"><?php echo esc_html( number_format_i18n( $count ) ); ?></span><?php endif; ?></span>
			<input id="<?php echo esc_attr( $input_id ); ?>" type="radio" name="<?php echo esc_attr( $widget_id . '-filter' ); ?>" value="<?php echo esc_attr( (string) $term_id ); ?>" <?php checked( $checked ); ?>>
			<span class="ffp-sheet__radio" aria-hidden="true"></span>
		</label>
		<?php
	}

	/**
	 * Allowed markup for built-in and Elementor-provided filter icons.
	 *
	 * @return array<string, array<string, bool>>
	 */
	private function get_filter_icon_allowed_html(): array {
		$global_attributes = array(
			'class'       => true,
			'aria-hidden' => true,
			'role'        => true,
			'style'       => true,
		);

		return array(
			'span'     => $global_attributes,
			'i'        => $global_attributes,
			'svg'      => array_merge(
				$global_attributes,
				array(
					'viewbox'           => true,
					'viewBox'           => true,
					'focusable'         => true,
					'xmlns'             => true,
					'width'             => true,
					'height'            => true,
					'fill'              => true,
					'stroke'            => true,
					'stroke-width'      => true,
					'stroke-linecap'    => true,
					'stroke-linejoin'   => true,
					'preserveaspectratio' => true,
				)
			),
			'g'        => $global_attributes,
			'path'     => array(
				'class'             => true,
				'd'                 => true,
				'fill'              => true,
				'fill-rule'         => true,
				'clip-rule'         => true,
				'stroke'            => true,
				'stroke-width'      => true,
				'stroke-linecap'    => true,
				'stroke-linejoin'   => true,
			),
			'rect'     => array( 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ),
			'circle'   => array( 'cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ),
			'ellipse'  => array( 'cx' => true, 'cy' => true, 'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ),
			'line'     => array( 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true ),
			'polyline' => array( 'points' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true ),
			'polygon'  => array( 'points' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linejoin' => true ),
			'use'      => array( 'href' => true, 'xlink:href' => true ),
			'title'    => array(),
		);
	}

	private function get_category_icon_map( $rows ): array {
		$map = array();
		if ( ! is_array( $rows ) ) { return $map; }
		foreach ( array_slice( $rows, 0, 100 ) as $row ) {
			$id = absint( $row['category_id'] ?? 0 );
			$icon = is_array( $row['icon'] ?? null ) ? $row['icon'] : array();
			if ( $id && ! empty( $icon['value'] ) ) { $map[ $id ] = $icon; }
		}
		return $map;
	}


	private function get_default_all_filter_icon(): array {
		return array( 'ffp_auto' => 'grid' );
	}

	private function get_default_icon_for_term( $term ): array {
		$name = '';
		$slug = '';

		if ( is_object( $term ) ) {
			$name = strtolower( sanitize_text_field( (string) ( $term->name ?? '' ) ) );
			$slug = strtolower( sanitize_key( (string) ( $term->slug ?? '' ) ) );
		}

		$haystack = trim( $slug . ' ' . $name );
		$map = array(
			'fortianalyzer' => 'chart',
			'fortigate'     => 'shield-grid',
			'fortimanager'  => 'briefcase',
			'fortisoar'     => 'gear',
			'network'       => 'nodes',
			'wordpress'     => 'wordpress',
			'python'        => 'code',
			'api'           => 'code',
			'develop'       => 'code',
			'coding'        => 'code',
			'sql'           => 'database',
			'design'        => 'pencil',
			'seo'           => 'chart',
			'analytics'     => 'chart',
			'report'        => 'chart',
			'marketing'     => 'megaphone',
			'business'      => 'briefcase',
			'automation'    => 'gear',
			'security'      => 'shield-grid',
			'firewall'      => 'shield-grid',
		);

		foreach ( $map as $needle => $icon_key ) {
			if ( false !== strpos( $haystack, $needle ) ) {
				return array( 'ffp_auto' => $icon_key );
			}
		}

		return array( 'ffp_auto' => 'tag' );
	}

	private function resolve_filter_icon( array $icon = array(), $term = null, bool $is_all = false ): array {
		if ( ! empty( $icon['value'] ) ) {
			return $icon;
		}

		return $is_all ? $this->get_default_all_filter_icon() : $this->get_default_icon_for_term( $term );
	}

	private function get_filter_icon_html( array $icon ): string {
		if ( ! empty( $icon['ffp_auto'] ) ) {
			$svg = $this->get_auto_filter_svg( sanitize_key( (string) $icon['ffp_auto'] ) );
			return '' !== $svg ? '<span class="ffp-chip__icon ffp-chip__icon--auto" aria-hidden="true">' . $svg . '</span>' : '';
		}

		if ( empty( $icon['value'] ) ) {
			return '';
		}

		ob_start();
		Icons_Manager::render_icon( $icon, array( 'aria-hidden' => 'true' ), 'span' );
		$markup = (string) ob_get_clean();

		return $markup ? '<span class="ffp-chip__icon" aria-hidden="true">' . $markup . '</span>' : '';
	}

	private function get_auto_filter_svg( string $icon_key ): string {
		$icons = array(
			'grid'        => '<svg viewBox="0 0 24 24" focusable="false"><rect x="4" y="4" width="6" height="6" rx="1"/><rect x="14" y="4" width="6" height="6" rx="1"/><rect x="4" y="14" width="6" height="6" rx="1"/><rect x="14" y="14" width="6" height="6" rx="1"/></svg>',
			'code'        => '<svg viewBox="0 0 24 24" focusable="false"><path d="m9 6-6 6 6 6M15 6l6 6-6 6M14 3l-4 18"/></svg>',
			'chart'       => '<svg viewBox="0 0 24 24" focusable="false"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/><path d="m4 8 5-4 6 5 5-4"/></svg>',
			'shield-grid' => '<svg viewBox="0 0 24 24" focusable="false"><path d="M12 3 20 6v5c0 5-3.4 8.7-8 10-4.6-1.3-8-5-8-10V6l8-3Z"/><rect x="8" y="8" width="3" height="3" rx=".4"/><rect x="13" y="8" width="3" height="3" rx=".4"/><rect x="8" y="13" width="3" height="3" rx=".4"/><rect x="13" y="13" width="3" height="3" rx=".4"/></svg>',
			'briefcase'   => '<svg viewBox="0 0 24 24" focusable="false"><path d="M9 6V4h6v2M4 7h16v13H4zM4 12h16M10 12v2h4v-2"/></svg>',
			'gear'        => '<svg viewBox="0 0 24 24" focusable="false"><path d="M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z"/><path d="m4.9 7.5-1.4-1.4 2.6-2.6L7.5 4.9A8 8 0 0 1 10 4V2h4v2a8 8 0 0 1 2.5.9l1.4-1.4 2.6 2.6-1.4 1.4A8 8 0 0 1 20 10h2v4h-2a8 8 0 0 1-.9 2.5l1.4 1.4-2.6 2.6-1.4-1.4A8 8 0 0 1 14 20v2h-4v-2a8 8 0 0 1-2.5-.9l-1.4 1.4-2.6-2.6 1.4-1.4A8 8 0 0 1 4 14H2v-4h2a8 8 0 0 1 .9-2.5Z"/></svg>',
			'nodes'       => '<svg viewBox="0 0 24 24" focusable="false"><circle cx="5" cy="12" r="2.5"/><circle cx="18" cy="5" r="2.5"/><circle cx="19" cy="18" r="2.5"/><path d="m7.2 10.8 8.6-4.6M7.4 13.1l9.2 3.8M18.3 7.5l.4 8"/></svg>',
			'wordpress'   => '<svg viewBox="0 0 24 24" focusable="false"><circle cx="12" cy="12" r="9"/><path d="m7.3 8.5 3 8 1.8-5 1.9 5 2.8-8M6.5 8.5h4M13.5 8.5h4"/></svg>',
			'database'    => '<svg viewBox="0 0 24 24" focusable="false"><ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v7c0 1.7 3.6 3 8 3s8-1.3 8-3V5M4 12v7c0 1.7 3.6 3 8 3s8-1.3 8-3v-7"/></svg>',
			'pencil'      => '<svg viewBox="0 0 24 24" focusable="false"><path d="m4 20 4.5-1 10-10-3.5-3.5-10 10L4 20ZM13.8 6.7l3.5 3.5M15 4.5l1.5-1.5 3.5 3.5L18.5 8"/></svg>',
			'megaphone'   => '<svg viewBox="0 0 24 24" focusable="false"><path d="M4 10v4h4l9 4V6L8 10H4ZM8 14l2 6h3l-2-5M19 9v6"/></svg>',
			'tag'         => '<svg viewBox="0 0 24 24" focusable="false"><path d="M3 12V5a2 2 0 0 1 2-2h7l9 9-9 9-9-9Z"/><circle cx="8" cy="8" r="1.5"/></svg>',
		);

		return $icons[ $icon_key ] ?? $icons['tag'];
	}

}
