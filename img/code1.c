#include <stdio.h>
#include <stdint.h>
#include <string.h>
#include <ctype.h>

// gcc code1.c -o code1

#define MAX_INPUT 48

// Name of the pet
char petName[4] = "cat";
uint8_t petSize = 3;
uint8_t proceed(char **c);

void move(char **s) {
    (*s)--;
}

void switchPets(char **s) {
    memcpy(petName, "duck", 4);
    petSize = 4;
}

void addPet(char **s) {
    char buf[4];
    memcpy(buf, petName, petSize);
    // Buffer index is zero
    uint8_t bufIndex = 0;

    while(buf[bufIndex]) {
        char temp = **s;
        **s = buf[bufIndex];
        buf[bufIndex++] = temp;
        if(bufIndex == petSize) bufIndex = 0;
        (*s)++;
    }
}

// Bad input handler (This genuinly disgusts me, though in a way it's also beautiful.)
void obliterate(char **s) {for(char *c = *s; *c; *c++ = 'E');}

void (*getFunc(char *input))(char **s) {
    struct funcName {
        char name[3];
        void (*f)(char**s);
    } 
    functions[] = {
        {"add", addPet},
        {"swc", switchPets},
        {"mov", move}
    };
    for(uint8_t i = 0; i < (sizeof(functions) / sizeof(*functions)); i++) {
        if(memcmp(input, functions[i].name, 3) == 0) {
            return functions[i].f;
        }
    }
    return obliterate;
}

int main(int argc, char* argv[]) {
    setbuf(stdout, NULL);
    char buffer[10] = {0};
    char *output = buffer;
    
    for(uint8_t commandIndex = 0; commandIndex < MAX_INPUT / 3; commandIndex++) {
        if(proceed(argv + 1)) break;
        for(uint8_t repeatCount = 0; repeatCount < (isdigit(*argv[1]) ? (*(argv[1]) - '0') : 1); repeatCount++)
            getFunc(argv[1] - 3)(&output);
        if(isdigit(*argv[1])) argv[1]++;
    }

    // Print the output
    puts(output);
    return 0;
}

uint8_t proceed(char **c) {
    for(uint8_t i = 0; i < 3; i++) {
        if(!*(*c)++) {
            return -1;
        }
    }
    return 0;
}
