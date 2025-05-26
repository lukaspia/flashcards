export const generatePath = (
    pathTemplate: string,
    params: { [key: string]: string | number } = {}
): string => {
    let generated = pathTemplate;
    for (const key in params) {
        if (Object.prototype.hasOwnProperty.call(params, key)) {
            generated = generated.replace(`:${key}`, String(params[key]));
        }
    }
    return generated;
};