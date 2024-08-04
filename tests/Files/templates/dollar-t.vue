<template>
    <div class="example-component">
        <h1>{{ $t("welcome_message") }}</h1>
        <p>{{ $t("description") }}</p>

        <div v-if="isLoggedIn">
            <p>{{ $t("logged_in") }}</p>
        </div>
        <div v-else>
            <p>{{ $t("please_log_in") }}</p>
        </div>

        <button @click="logInUser">{{ $t("log_in") }}</button>

        <div v-for="item in items" :key="item.id">
            {{ $t("item", { count: item.count }) }}
        </div>

        <CurrentButton @click="buttonClicked">
            {{ $t("click_me") }}
        </CurrentButton>
    </div>
</template>

<script>
export default {
    name: "ExampleComponent",
    data() {
        return {
            isLoggedIn: false,
            items: [
                { id: 1, count: 1 },
                { id: 2, count: 2 },
            ],
            currentButton: "button",
        };
    },
    methods: {
        logInUser() {
            this.isLoggedIn = true;
        },
        buttonClicked() {
            this.$emit("Clicked");
            alert(this.$t("button_clicked"));
        },
    },
};
</script>

<style scoped></style>
