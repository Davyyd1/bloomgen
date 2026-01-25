import { usePage } from "@inertiajs/react";
import { useEffect, useRef } from "react";
import toast from "react-hot-toast";

export default function FlashToasts() {
    const {flash} = usePage().props;
    const last = useRef({ success: null, error: null, info: null, warning: null });
    useEffect(() => {
        if(flash?.success && flash.success !== last.current.success){
            toast.success(flash.success);
            last.current.success = flash.success;
        }

        if (flash?.error && flash.error !== last.current.error) {
            toast.error(flash.error);
            last.current.error = flash.error;
        }

        if (flash?.info && flash.info !== last.current.info) {
            toast(flash.info);
            last.current.info = flash.info;
        }

        if (flash?.warning && flash.warning !== last.current.warning) {
        toast(flash.warning, { icon: '⚠️' });
        last.current.warning = flash.warning;
        }
    },[[flash?.success, flash?.error, flash?.info, flash?.warning]])

    return null;
}