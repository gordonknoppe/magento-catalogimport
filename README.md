Guidance Catalogimport
===============================================

A Magento module that offers improvements over the out-of-box dataflow imports.


Features
-------------------------------------------------------

1. Import Configurable products and specify configurable attributes.
2. Import Grouped products, and associate Simple products.
2. Associate imported Simple products to Configurable and Grouped.
3. Overwrite images uploaded to products.
4. Delete all images for a given product.
4. Import Categories. Optionally provide category keys that will remain consistent ID's across environments.
5. Import product to category relationship, including product position.



Usage Overview
-------------------------------------------------------

The Guidance module splits the catalog imports into serveral logical passes.

- Import simple products using "Guidance Add/Update Simple Products".
- Import complex products using "Guidance Add/Update Configurable Products".
- Import products images using "Guidance Add/Update Product Images".

