import "@fontsource-variable/inter";
import Alpine from "alpinejs";
import persist from "@alpinejs/persist";
import focus from "@alpinejs/focus";
import collapse from "@alpinejs/collapse";

Alpine.plugin(persist);
Alpine.plugin(focus);
Alpine.plugin(collapse);

window.Alpine = Alpine;
Alpine.start();
