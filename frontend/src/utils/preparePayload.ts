// src/utils/preparePayload.ts

function toSnakeCase(key: string): string {
  return key
    .replace(/([A-Z])/g, "_$1")
    .replace(/__/g, "_")
    .toLowerCase();
}

export function preparePayload(input: any): any {
  if (Array.isArray(input)) {
    return input.map(preparePayload);
  } else if (input !== null && typeof input === "object") {
    const newObj: Record<string, any> = {};
    for (const key in input) {
      if (Object.prototype.hasOwnProperty.call(input, key)) {
        const snakeKey = toSnakeCase(key);
        newObj[snakeKey] = preparePayload(input[key]);
      }
    }
    return newObj;
  } else {
    return input;
  }
}
