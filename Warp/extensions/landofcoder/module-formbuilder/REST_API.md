# API Doc

List common REST API of formbuilder

## 1. Get Form Profile with Design Fields

Endpoint: [Domain]/V1/lof-formbuilder/getForm/{formId}/{customerGroupId}/{storeId}

Params:
- formId : Int - form id
- customerGroupId : Int - customer group id
- storeId : Int - current store id

Response:

```
{
  "form": {
    "form_id": 0,
    "title": "string",
    "status": 0,
    "identifier": "string",
    "email_receive": "string",
    "thanks_email_template": "string",
    "email_template": "string",
    "show_captcha": 0,
    "show_toplink": 0,
    "submit_button_text": "string",
    "success_message": "string",
    "creation_time": "string",
    "update_time": "string",
    "before_form_content": "string",
    "after_form_content": "string",
    "design": "string",
    "page_title": "string",
    "redirect_link": "string",
    "page_layout": "string",
    "meta_keywords": "string",
    "meta_description": "string",
    "thankyou_field": "string",
    "thankyou_email_template": "string",
    "submit_text_color": "string",
    "submit_background_color": "string",
    "submit_hover_color": "string",
    "input_hover_color": "string",
    "custom_template": "string",
    "sender_email_field": "string",
    "sender_name_field": "string",
    "tags": "string",
    "enable_tracklink": 0,
    "customer_groups": "string",
    "design_fields": [
      {
        "label": "string",
        "field_type": "string",
        "required": true,
        "field_options": "string",
        "fieldcol": 0,
        "wrappercol": 0,
        "cid": "string",
        "field_id": "string",
        "inline_css": "string",
        "field_size": "string",
        "font_weight": "string",
        "color_text": "string",
        "font_size": "string",
        "color_label": "string",
        "validation": "string",
        "include_blank_option": "string",
        "options": [
          {
            "label": "string",
            "checked": true
          }
        ]
      }
    ]
  },
  "fields": [
    {
      "label": "string",
      "field_type": "string",
      "required": true,
      "field_options": "string",
      "fieldcol": 0,
      "wrappercol": 0,
      "cid": "string",
      "field_id": "string",
      "inline_css": "string",
      "field_size": "string",
      "font_weight": "string",
      "color_text": "string",
      "font_size": "string",
      "color_label": "string",
      "validation": "string",
      "include_blank_option": "string",
      "options": [
        {
          "label": "string",
          "checked": true
        }
      ]
    }
  ]
}
```

2. Submit form data

Endpoint: [Domain]/rest/V1/lof-formbuilder/form
Params:

```
{
  "formData": {
    "form_id": Int,
    "product_id": Int,
    "captcha": String,
    "fields": [
      {
        "cid": String,
        "field_name": String,
        "value": String
      }
    ]
  },
  "storeId": Int
}
```

- form_id : Int - form id, required
- product_id : Int - Current product id, can empty
- captcha : String - recaptcha response code, can empty
- cid : String - field cid
- field_name : String - field name
- value : String - submitted field value

Response: Int - message ID
