export const generatePath = (
    pathTemplate: string,
    params: { [key: string]: string | number } = {}
): string => {
    let generatedPath = pathTemplate;

    for (const key in params) {
        if (Object.prototype.hasOwnProperty.call(params, key)) {
            const value = params[key];
            generatedPath = generatedPath.replace(new RegExp(`:${key}(?![a-zA-Z0-9_])`, 'g'), String(value));
        }
    }

    return generatedPath;
};