<script>
    export let type = ""; // success, info, warning, danger
    export let message = "";
    export let link = "";

    let noticeClass = "helpiefaq__notice--" + type;
    let iconClass = "";
    if (type == "success") {
        iconClass = "yes-alt";
    } else if (type == "info") {
        iconClass = "info";
    } else if (type == "warning") {
        iconClass = "warning";
    } else if (type == "danger") {
        iconClass = "dismiss";
    }

    let linkAttributes = "";
    if (link && link.open_in_new_tab) {
        linkAttributes = "target='_blank'";
    }
</script>

<div class="helpiefaq__notice {noticeClass}">
    <span class="dashicons dashicons-{iconClass}" />
    <span class="helpiefaq__notice__text">
        {message}&nbsp;
        {#if link}
            <a class="helpiefaq__notice__link" href={link.url} {linkAttributes}>
                {link.text} >></a
            >
        {/if}
    </span>
</div>

<style>
    .helpiefaq__notice {
        @apply helpiefaq-text-white helpiefaq-py-3 helpiefaq-px-1 helpiefaq-border-0 helpiefaq-rounded helpiefaq-relative helpiefaq-mb-2 helpiefaq-flex helpiefaq-items-center;
    }

    .helpiefaq__notice--success {
        @apply helpiefaq-bg-green-500;
    }

    .helpiefaq__notice--info {
        @apply helpiefaq-bg-blue-400;
    }

    .helpiefaq__notice--warning {
        @apply helpiefaq-bg-amber-500;
    }

    .helpiefaq__notice--danger {
        @apply helpiefaq-bg-red-400;
    }

    .helpiefaq__notice__text {
        @apply helpiefaq-inline-block helpiefaq-align-middle helpiefaq-ml-1;
    }

    .helpiefaq__notice__link {
        @apply helpiefaq-no-underline helpiefaq-whitespace-nowrap;
    }
    .helpiefaq__notice__link:hover {
        @apply helpiefaq-underline;
    }
</style>
