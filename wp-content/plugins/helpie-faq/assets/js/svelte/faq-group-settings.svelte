<script context="module">
    import { onMount } from "svelte";
    import TailwindCSS from "./tailwindcss.svelte";
    import Select from "svelte-select";
    import Notice from "./notice.svelte";
</script>

<script>
    // export let categories = [];
    export let products = [];
    // export let tagID = 0;
    export let settings;
    export let pageAction;
    export let productCategories = [];

    const isPremium = helpie_faq_object["faq_plan"] === "premium";
    const trialURL = helpie_faq_object["trial_url"];
    const isWoocommerceActive =
        helpie_faq_object["supported_plugins"]["woocommerce"] === "active";

    let selectedProducts = [];
    let selectedProductCategories = [];

    let groupStyles = {
        header: {
            background:
                typeof settings.header != "undefined" &&
                typeof settings.header.background != "undefined"
                    ? settings.header.background
                    : "",
            color:
                typeof settings.header != "undefined" &&
                typeof settings.header.color != "undefined"
                    ? settings.header.color
                    : "",
        },
        body: {
            background:
                typeof settings.body != "undefined" &&
                typeof settings.body.background != "undefined"
                    ? settings.body.background
                    : "",
            color:
                typeof settings.body != "undefined" &&
                typeof settings.body.color != "undefined"
                    ? settings.body.color
                    : "",
        },
    };

    let sideNavStyle = "";
    let angle = "left";
    let sidebarToggleTitle = "Close";

    onMount(async () => {
        jQuery(".helpiefaq__colorPicker").wpColorPicker({
            change: function (event, ui) {
                const elementID = event.target.id;
                const color = ui.color.toString();
                setColor(elementID, color);
            },
            clear: function (event) {
                let element = jQuery(event.target)
                    .parent()
                    .find(".helpiefaq__colorPicker");
                const elementID = jQuery(element).attr("id");
                setColor(elementID, "");
            },
        });
    });

    if (
        typeof settings.products !== "undefined" &&
        settings.products.length > 0
    ) {
        for (let ii = 0; ii < settings.products.length; ii++) {
            let productID = settings.products[ii];
            if (productID != "0") {
                const selectedProduct = products.find(
                    (product) => product.value == productID
                );
                if (typeof selectedProduct !== "undefined") {
                    selectedProducts.push({
                        value: productID,
                        label: selectedProduct.label,
                    });
                }
            }
        }
    }

    if (
        typeof settings.product_categories !== "undefined" &&
        settings.product_categories.length > 0
    ) {
        for (let ii = 0; ii < settings.product_categories.length; ii++) {
            let productCategoryID = settings.product_categories[ii];
            if (
                typeof productCategoryID !== "undefined" &&
                productCategoryID != "0"
            ) {
                const selectedProductCategory = productCategories.find(
                    (category) => category.value == productCategoryID
                );
                if (typeof selectedProductCategory !== "undefined") {
                    selectedProductCategories.push({
                        value: productCategoryID,
                        label: selectedProductCategory.label,
                    });
                }
            }
        }
    }

    const toggleSideMenu = () => {
        if (angle == "left") {
            angle = "right";
            sideNavStyle = "min-width: 36px; width: 36px;";
            sidebarToggleTitle = "Open";
        } else {
            angle = "left";
            sideNavStyle = "min-width: 300px; width: 300px;";
            sidebarToggleTitle = "Close";
        }
    };

    const formTagName = pageAction == "create" ? "#addtag" : "#edittag";

    $: updateStyleFieldsValues(groupStyles);
    $: updateProductFieldsValues(selectedProducts);
    $: updateProductCategoryFieldsValues(selectedProductCategories);

    function updateStyleFieldsValues(groupStyles) {
        jQuery('input[name="faq_group_settings[header][background]"]').val(
            groupStyles.header.background
        );

        jQuery('input[name="faq_group_settings[header][color]"]').val(
            groupStyles.header.color
        );

        jQuery('input[name="faq_group_settings[body][background]"]').val(
            groupStyles.body.background
        );

        jQuery('input[name="faq_group_settings[body][color]"]').val(
            groupStyles.body.color
        );
    }

    function updateProductFieldsValues(selectedProducts) {
        jQuery('input[name="faq_group_settings[products][]"]').remove();

        if (selectedProducts && selectedProducts.length > 0) {
            let hiddenElements = "";
            selectedProducts.forEach(function (product) {
                hiddenElements +=
                    '<input type="hidden" name="faq_group_settings[products][]" value="' +
                    product.value +
                    '" />';
            });
            jQuery(formTagName).append(hiddenElements);
        }
    }

    function updateProductCategoryFieldsValues(selectedProductCategories) {
        jQuery(
            'input[name="faq_group_settings[product_categories][]"]'
        ).remove();

        if (selectedProductCategories && selectedProductCategories.length > 0) {
            let hiddenElements = "";
            selectedProductCategories.forEach(function (category) {
                hiddenElements +=
                    '<input type="hidden" name="faq_group_settings[product_categories][]" value="' +
                    category.value +
                    '" />';
            });
            jQuery(formTagName).append(hiddenElements);
        }
    }

    function setColor(elementID, color) {
        if (elementID == "faq__header_background") {
            groupStyles.header.background = color;
        } else if (elementID == "faq__header_color") {
            groupStyles.header.color = color;
        } else if (elementID == "faq__body_background") {
            groupStyles.body.background = color;
        } else if (elementID == "faq__body_color") {
            groupStyles.body.color = color;
        }
    }
</script>

<TailwindCSS />

<div style={sideNavStyle} class="helpiefaq__group helpiefaq__sidebarnav">
    <span class:helpiefaq__sidebarnav--overlay={angle == "right"} />
    <span
        on:click={toggleSideMenu}
        class="helpiefaq__sidebarnav--toggle dashicons dashicons-arrow-{angle}-alt"
        title={sidebarToggleTitle}
    />

    <div class="helpiefaq__group__settings">
        <div>
            <div class="helpiefaq__title">
                Settings
                {#if !isPremium}
                    <span class="helpiefaq__premiumTag">PRO</span>
                {/if}
            </div>
            {#if !isPremium}
                <Notice
                    type="warning"
                    message="This feature is only available in the premium plan..."
                    link={{
                        url: trialURL,
                        text: "Upgrade to PRO now",
                    }}
                />
            {/if}
        </div>

        <label class="helpiefaq__heading">FAQ Header Styles</label>
        <div class="helpiefaq__fields__container">
            <div class="helpiefaq__input-group">
                <label class="helpiefaq__label"> Background </label>
                <input
                    class="helpiefaq__colorPicker"
                    id="faq__header_background"
                    type="text"
                    bind:value={groupStyles.header.background}
                />
            </div>
            <div class="helpiefaq__input-group">
                <label class="helpiefaq__label"> Font Color </label>
                <input
                    class="helpiefaq__colorPicker"
                    id="faq__header_color"
                    type="text"
                    bind:value={groupStyles.header.color}
                />
            </div>
        </div>

        <label class="helpiefaq__heading">FAQ Body Styles</label>

        <div class="helpiefaq__fields__container">
            <div class="helpiefaq__input-group">
                <label class="helpiefaq__label"> Background </label>
                <input
                    class="helpiefaq__colorPicker"
                    id="faq__body_background"
                    type="text"
                    bind:value={groupStyles.body.background}
                />
            </div>

            <div class="helpiefaq__input-group">
                <label class="helpiefaq__label"> Font Color </label>
                <input
                    class="helpiefaq__colorPicker"
                    id="faq__body_color"
                    type="text"
                    bind:value={groupStyles.body.color}
                />
            </div>
        </div>

        <label class="helpiefaq__heading">Products</label>
        {#if isPremium && isWoocommerceActive}
            <Notice
                type="info"
                message="The above styles would not be applied on the products page. The products page style would come from Global Settings."
            />
        {/if}
        {#if !isWoocommerceActive}
            <Notice
                type="info"
                message="Please activate the WooCommerce plugin for this setting to work."
            />
        {/if}
        <div class="helpiefaq__select--products">
            <Select
                items={products}
                isMulti={true}
                bind:value={selectedProducts}
            />
        </div>

        <label class="helpiefaq__heading">Product Categories</label>
        {#if !isWoocommerceActive}
            <Notice
                type="info"
                message="Please activate the WooCommerce plugin for this setting to work."
            />
        {/if}
        <div class="helpiefaq__select--categories">
            <Select
                items={productCategories}
                isMulti={true}
                bind:value={selectedProductCategories}
            />
        </div>
    </div>
</div>

<style>
    .helpiefaq__sidebarnav--overlay {
        @apply helpiefaq-top-0 helpiefaq-right-0 helpiefaq-left-0 helpiefaq-bottom-0 helpiefaq-bg-gray-300 helpiefaq-absolute helpiefaq-z-10 helpiefaq-h-full helpiefaq-flex;
    }

    .helpiefaq__sidebarnav--toggle {
        @apply helpiefaq-p-2 helpiefaq-absolute helpiefaq-top-1 helpiefaq-right-0 helpiefaq-rounded-l-lg helpiefaq-bg-gray-200 hover:helpiefaq-cursor-pointer helpiefaq-z-10;
    }

    .helpiefaq__sidebarnav {
        width: 300px;
        min-width: 300px;
        min-height: 700px;
        max-height: 1280px;
        background: #f0f0f1;

        @apply helpiefaq-border-gray-300 helpiefaq-border-solid helpiefaq-border helpiefaq-top-0 helpiefaq-left-0 helpiefaq-z-10  helpiefaq-overflow-x-hidden helpiefaq-h-full helpiefaq-relative helpiefaq-transition-all helpiefaq-duration-500;
    }

    .helpiefaq__group__settings {
        @apply helpiefaq-mt-12 helpiefaq-ml-2 helpiefaq-mr-2 helpiefaq-mb-2 helpiefaq-border-2 helpiefaq-border-solid helpiefaq-border-gray-300 helpiefaq-rounded-lg helpiefaq-p-2 helpiefaq-bg-white;
    }

    .helpiefaq__title {
        @apply helpiefaq-text-gray-700 helpiefaq-font-bold helpiefaq-text-xl helpiefaq-ml-1;
    }

    .helpiefaq__label {
        @apply helpiefaq-block helpiefaq-text-sm helpiefaq-font-medium helpiefaq-text-gray-700 helpiefaq-mt-1 helpiefaq-mb-1;
    }

    .helpiefaq__heading {
        @apply helpiefaq-block helpiefaq-text-base helpiefaq-font-semibold helpiefaq-text-gray-600 helpiefaq-mt-1 helpiefaq-mb-1;
    }

    .helpiefaq__fields__container {
        @apply helpiefaq-flex helpiefaq-flex-wrap;
    }

    .helpiefaq__input-group {
        padding: 0 0.5rem 0 0;
    }
</style>
