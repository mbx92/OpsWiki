export function useCopy() {
    const copy = async (text) => {
        await navigator.clipboard.writeText(text);
    };

    return { copy };
}
