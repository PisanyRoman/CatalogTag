<?php
class ControllerCommonMaincattag extends Controller {
	public function index() {  
        $this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));
		if (isset($this->request->get['route'])) {
			$this->document->addLink($this->config->get('config_url'), 'canonical');
		}
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
        
        $this->load->language('common/maincattag');
        
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_maincattag'),
			'href' => $this->url->link('common/maincattag')
		);
        
		$this->load->model('catalog/category');
		
        $this->load->model('catalog/product');
        
        $this->load->model('tool/image');
        
		$data['maincattagcats'] = array();
        
        $data['productstags'][] = array();

		$categories = $this->model_catalog_category->getCategories(0);
		
        foreach ($categories as $category) {
			if ($category['top']) {
                $childcategories = $this->model_catalog_category->getCategories($category['category_id']);
                foreach ($childcategories as $child) {
                    $filter_data = array(
                    'filter_tag'          => $this->request->get['filter_tag'],
                    'filter_category_id'  => $child['category_id'],
                    'filter_sub_category' => true
                    );
                    $productstags = $this->model_catalog_product->getProducts($filter_data);
                    
                    foreach ($productstags as $productsar) {
                        if ($productsar['image']) {
                           $image = $this->model_tool_image->resize($productsar['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
                        } else {
                           $image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
                        }    
                        
                        if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                           $price = $this->currency->format($this->tax->calculate($productsar['price'], $productsar['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                        } else {
                           $price = false;
                        }

                        if ((float)$productsar['special']) {
                           $special = $this->currency->format($this->tax->calculate($productsar['special'], $productsar['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                        } else {
                           $special = false;
                        }

                        if ($this->config->get('config_tax')) {
                           $tax = $this->currency->format((float)$productsar['special'] ? $productsar['special'] : $productsar['price'], $this->session->data['currency']);
                        } else {
                           $tax = false;
                        }

                        if ($this->config->get('config_review_status')) {
                           $rating = (int)$productsar['rating'];
                        } else {
                           $rating = false;
                        }    
                        
                        
                        $data['productstags'][] = array(
                            'product_id'     => $productsar['product_id'],
                            'name'           => $productsar['name'],
                            'thumb'          => $image,
                            'href'           => $this->url->link('product/product', 'product_id=' . $productsar['product_id']),
                            'description' => utf8_substr(trim(strip_tags(html_entity_decode($productsar['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
                            'price'       => $price,
                            'special'     => $special,
                            'tax'         => $tax,
                            'minimum'     => $productsar['minimum'] > 0 ? $productsar['minimum'] : 1,
                            'rating'      => $productsar['rating'],
                            'categories'     => $child['name']
                              );  
                    }
                    
                    $data['childcattags'][] = array(
                    'category_id'    => $child['category_id'],
                    'name'           => $child['name']
		              );            
                } 
            
            }
            
        }
          
        $data['cat_options'] = array();

		foreach ($this->model_catalog_category->getCatProductOptions() as $cat_option) {
            $price_cur = round($cat_option['price'] * $cat_option['price_curs']);
            $data['cat_options'][] = array(
					'product_id'        => $cat_option['product_id'],
				    'option_id'         => $cat_option['option_id'],
				    'option_value_id'   => $cat_option['option_value_id'],
				    'price'             => $price_cur
            );
		}
                
        if (isset($this->request->get['title'])) {
            $title = $this->request->get['title'];
		} else {
            $title = '';
		}
        
        $data['title'] = $title;

        $this->response->setOutput($this->load->view('common/maincattag', $data));
	}
}