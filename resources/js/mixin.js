import Vue from "vue";

Vue.mixin({
    methods: {
        sweetAlertSucessToast(text) {
            this.$swal({
                toast: true,
                position: "top-right",
                icon: "success",
                text: text,
                showConfirmButton: false,
                customClass: {
                    container: "sweet-toast-custom-success",
                },
                timer: 3000,
            });
            $("body").removeClass("swal2-height-auto");
        },
        sweetAlertErrorToast(text) {
            this.$swal({
                toast: true,
                position: "top-right",
                icon: "error",
                text: text,
                showConfirmButton: false,
                customClass: {
                    container: "sweet-toast-custom-error",
                },
                timer: 3000
            });
            $("body").removeClass("swal2-height-auto");
        },
    },
});
